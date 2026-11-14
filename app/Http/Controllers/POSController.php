<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\GiftCard;
use App\Models\Discount;
use App\Models\Register;
use App\Services\POSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;

class POSController extends Controller
{
    public function __construct(private POSService $posService) {}

    /** Show the main POS screen. */
    public function index()
    {
        $user       = Auth::user();
        $storeId    = $user->store_id;
        $store      = \App\Models\Store::find($storeId);
        $register   = Register::where('store_id', $storeId)->where('status', 'open')->latest()->first();
        $categories = \App\Models\Category::where('store_id', $storeId)->where('is_active', true)->whereNull('parent_id')->withCount('products')->get();

        // Bill-only mode data
        $userPaymentMode = $user->isBillOnlyMode() ? 'bill_only' : 'full';
        $seasonMode      = $store?->settings['season_mode'] ?? false;
        $canProcessPayment = $user->canProcessPayment();

        return view('pos.index', compact('user', 'store', 'register', 'categories', 'userPaymentMode', 'seasonMode', 'canProcessPayment'));
    }

    /** Customer Display - Standalone page that syncs via localStorage */
    public function customerDisplay()
    {
        $store = \App\Models\Store::find(Auth::user()->store_id);
        return view('pos.customer-display', compact('store'));
    }

    /** Search products for POS with pagination. */
    public function searchProducts(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $term    = $request->get('q', '');
        $catId   = $request->get('category_id');
        $page    = (int) $request->get('page', 1);
        $perPage = 40; // Load 40 products per page for fast loading

        // Build base query
        $baseQuery = Product::where('store_id', $storeId)->where('is_active', true);

        if ($catId) {
            $baseQuery->where('category_id', $catId);
        }
        if ($term) {
            $baseQuery->search($term);
        }

        // Get total count using a clone
        $total = (clone $baseQuery)->count();

        // Get paginated products
        $products = $baseQuery->orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->with(['category', 'variants', 'tax'])
            ->get()
            ->map(function ($p) {
                return [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'sku'           => $p->sku,
                    'barcode'       => $p->barcode,
                    'selling_price' => $p->selling_price,
                    'cost_price'    => $p->cost_price,
                    'stock'         => $p->stock_quantity,
                    'unit'          => $p->unit,
                    'image'         => $p->image ? asset(str_starts_with($p->image, 'storage/') ? $p->image : 'storage/' . $p->image) : null,
                    'category'      => $p->category?->name,
                    'track_stock'   => $p->track_stock,
                    'allow_negative'=> $p->allow_negative_stock,
                    'tax_rate'      => $p->tax?->rate ?? 0,
                    'has_variants'  => $p->has_variants,
                    'variants'      => $p->has_variants ? $p->variants->map(fn($v) => [
                        'id'    => $v->id, 'name' => $v->name,
                        'price' => $v->selling_price, 'stock' => $v->stock_quantity,
                    ]) : [],
                ];
            });

        return response()->json([
            'products'    => $products,
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'has_more'    => ($page * $perPage) < $total,
        ]);
    }

    /** Search customers for POS. */
    public function searchCustomers(Request $request)
    {
        $storeId  = Auth::user()->store_id;
        $term     = $request->get('q', '');
        $customers = Customer::where('store_id', $storeId)
            ->where('is_active', true)
            ->search($term)
            ->limit(10)
            ->get(['id','uuid','name','phone','email','loyalty_points','current_balance']);
        return response()->json(['customers' => $customers]);
    }

    /** Process a sale. */
    public function processSale(Request $request)
    {
        $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer',
            'items.*.quantity'     => 'required|numeric|min:0.001',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'payments'             => 'required|array|min:1',
            'payments.*.method'    => 'required|string',
            'payments.*.amount'    => 'required|numeric|min:0',
        ]);

        try {
            $order = $this->posService->processSale($request->all());
            return response()->json([
                'success' => true,
                'order'   => $order,
                'message' => 'Sale completed successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** Create a bill without payment (bill-only mode). */
    public function createBill(Request $request)
    {
        $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer',
            'items.*.quantity'     => 'required|numeric|min:0.001',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ]);

        try {
            $order = $this->posService->createBill($request->all());
            return response()->json([
                'success' => true,
                'order'   => $order,
                'message' => 'Bill created successfully! Awaiting payment approval.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** Get pending payment orders for approval queue. */
    public function getPendingPayments()
    {
        $storeId = Auth::user()->store_id;
        $orders  = Order::where('store_id', $storeId)
            ->where('status', 'pending_payment')
            ->with(['customer', 'items.product', 'billedBy'])
            ->latest()
            ->get();
        return response()->json(['orders' => $orders]);
    }

    /** Approve and process payment for a pending order. */
    public function approvePayment(Request $request, Order $order)
    {
        $request->validate([
            'payments'             => 'required|array|min:1',
            'payments.*.method'    => 'required|string',
            'payments.*.amount'    => 'required|numeric|min:0',
            'paid_amount'          => 'required|numeric|min:0',
        ]);

        $user    = Auth::user();
        $storeId = $user->store_id;

        // Verify order belongs to store and user can process payments
        if ($order->store_id !== $storeId) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if (!$user->canProcessPayment()) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to process payments.'], 403);
        }

        try {
            $order = $this->posService->approvePayment($order, $request->all());
            return response()->json([
                'success' => true,
                'order'   => $order,
                'message' => 'Payment approved successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** Cancel a pending payment order and restore stock. */
    public function cancelPendingBill(Request $request, Order $order)
    {
        $storeId = Auth::user()->store_id;

        // Verify order belongs to store
        if ((int) $order->store_id !== (int) $storeId) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        try {
            $reason = $request->input('reason', 'Cancelled from POS');
            $order = $this->posService->cancelPendingBill($order, $reason);

            return response()->json([
                'success' => true,
                'order'   => $order,
                'message' => 'Pending bill cancelled successfully and stock restored.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** Hold an order. */
    public function holdOrder(Request $request)
    {
        try {
            $order = $this->posService->holdOrder($request->all());
            return response()->json(['success' => true, 'order' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** Get held orders. */
    public function getHeldOrders()
    {
        $storeId = Auth::user()->store_id;
        $orders  = Order::where('store_id', $storeId)
            ->where('status', 'on_hold')
            ->with(['customer', 'items.product'])
            ->latest()
            ->get();
        return response()->json(['orders' => $orders]);
    }

    /** Retrieve a held order (load into cart). */
    public function retrieveHeldOrder(Order $order)
    {
        $storeId = Auth::user()->store_id;

        // Verify order belongs to store and is on_hold
        if ($order->store_id !== $storeId || $order->status !== 'on_hold') {
            return response()->json(['success' => false, 'message' => 'Order not found or already processed.'], 404);
        }

        // Load items with product details
        $order->load(['items.product', 'customer']);

        // Format items for cart
        $cartItems = $order->items->map(function ($item) {
            $product = $item->product;
            return [
                'product_id'   => $item->product_id,
                'variant_id'   => null,
                'product_name' => $item->product_name,
                'sku'          => $item->sku,
                'barcode'      => $item->barcode,
                'quantity'     => (float) $item->quantity,
                'unit'         => $item->unit,
                'unit_price'   => (float) $item->unit_price,
                'cost_price'   => (float) $item->cost_price,
                'tax_rate'     => (float) $item->tax_rate,
                'stock'        => $product ? (float) $product->stock_quantity : 0,
                'track_stock'  => $product ? $product->track_stock : false,
                'discount_amount' => (float) $item->discount_amount,
                'total'        => (float) ($item->quantity * $item->unit_price),
            ];
        });

        return response()->json([
            'success'  => true,
            'order'    => $order,
            'items'    => $cartItems,
            'customer' => $order->customer,
            'discount' => [
                'type'   => $order->discount_type ?? 'none',
                'value'  => (float) ($order->discount_value ?? 0),
                'amount' => (float) ($order->discount_amount ?? 0),
            ],
        ]);
    }

    /** Delete a held order. */
    public function deleteHeldOrder(Order $order)
    {
        $storeId = Auth::user()->store_id;

        // Verify order belongs to store and is on_hold
        if ($order->store_id !== $storeId || $order->status !== 'on_hold') {
            return response()->json(['success' => false, 'message' => 'Order not found or already processed.'], 404);
        }

        // Delete the order items first, then the order
        $order->items()->delete();
        $order->delete();

        return response()->json(['success' => true, 'message' => 'Held order deleted.']);
    }

    /** Apply coupon. */
    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string', 'subtotal' => 'required|numeric']);
        $storeId = Auth::user()->store_id;
        $result  = $this->posService->applyCoupon($request->code, $request->subtotal, $storeId);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /** Validate gift card. */
    public function validateGiftCard(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $storeId  = Auth::user()->store_id;
        $giftCard = GiftCard::where('store_id', $storeId)->where('code', $request->code)->first();

        if (!$giftCard || !$giftCard->isActive()) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired gift card.'], 422);
        }

        return response()->json([
            'success' => true,
            'balance' => $giftCard->current_balance,
            'message' => "Gift card valid. Balance: Rs. " . number_format($giftCard->current_balance, 2),
        ]);
    }

    /** Open register. */
    public function openRegister(Request $request)
    {
        $request->validate(['opening_balance' => 'required|numeric|min:0']);
        $user = Auth::user();
        $register = Register::create([
            'store_id'        => $user->store_id,
            'user_id'         => $user->id,
            'name'            => 'Register ' . date('Y-m-d H:i'),
            'opening_balance' => $request->opening_balance,
            'status'          => 'open',
            'opened_at'       => now(),
        ]);
        return response()->json(['success' => true, 'register' => $register]);
    }

    /** Close register. */
    public function closeRegister(Request $request, Register $register)
    {
        $user = Auth::user();
        $storeId = (int) $user->store_id;

        // Verify store ownership
        if ((int) $register->store_id !== $storeId) {
            return response()->json(['success' => false, 'message' => 'Register not found.'], 404);
        }

        // Verify register is currently open
        if ($register->status !== 'open') {
            return response()->json(['success' => false, 'message' => 'Register session is already closed.'], 422);
        }

        try {
            return DB::transaction(function () use ($request, $register, $storeId) {
                // Lock register row for update
                $register = Register::where('id', $register->id)
                    ->where('store_id', $storeId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($register->status !== 'open') {
                    throw new \Exception('Register session is already closed.');
                }

                $sessionStart = $register->opened_at ?? now()->subHours(8);
                $sessionEnd   = now();

                // Retrieve all payments received during this register session for this store
                $sessionPayments = Payment::whereHas('order', function ($q) use ($storeId) {
                        $q->where('store_id', $storeId);
                    })
                    ->where('created_at', '>=', $sessionStart)
                    ->where('created_at', '<=', $sessionEnd)
                    ->with('order')
                    ->get();

                $grossCashPayments = 0.0;
                $cardPayments      = 0.0;
                $otherPayments     = 0.0;
                $cashRefunds       = 0.0;

                // Group cash payments by order_id so order change is allocated accurately per order
                $cashPaymentsByOrder = [];

                foreach ($sessionPayments as $payment) {
                    $amount = (float) $payment->amount;

                    // Deduct refund only when the application explicitly records a verified completed cash payout on a return order
                    if ($payment->method === 'cash' && $payment->status === 'completed' && $payment->order?->order_type === 'return') {
                        $cashRefunds += abs($amount);
                        continue;
                    }

                    // Only count completed positive payments on sales orders
                    if ($payment->status !== 'completed' || $amount <= 0 || $payment->order?->order_type === 'return') {
                        continue;
                    }

                    if ($payment->method === 'cash') {
                        $grossCashPayments += $amount;
                        $orderId = (int) $payment->order_id;
                        if (!isset($cashPaymentsByOrder[$orderId])) {
                            $cashPaymentsByOrder[$orderId] = [
                                'order'         => $payment->order,
                                'cash_tendered' => 0.0,
                            ];
                        }
                        $cashPaymentsByOrder[$orderId]['cash_tendered'] += $amount;
                    } elseif ($payment->method === 'card') {
                        $cardPayments += $amount;
                    } else {
                        $otherPayments += $amount;
                    }
                }

                // Calculate change given from cash drawer and net cash sales per order
                $totalCashChange = 0.0;
                $netCashSales    = 0.0;

                foreach ($cashPaymentsByOrder as $orderId => $info) {
                    $order = $info['order'];
                    $cashTendered = $info['cash_tendered'];
                    $orderChange  = $order ? max(0.0, (float) ($order->change_amount ?? 0)) : 0.0;

                    // Allocate change against the cash portion only:
                    // Change given from drawer cannot exceed the cash tendered for this order.
                    $cashChangeGiven = min($cashTendered, $orderChange);

                    $totalCashChange += $cashChangeGiven;
                    $netCashSales    += ($cashTendered - $cashChangeGiven);
                }

                $grossCashPayments = round($grossCashPayments, 2);
                $totalCashChange   = round($totalCashChange, 2);
                $netCashSales      = round($netCashSales, 2);
                $cardPayments      = round($cardPayments, 2);
                $otherPayments     = round($otherPayments, 2);
                $cashRefunds       = round($cashRefunds, 2);

                // Calculate session order stats (completed during this session)
                $sessionOrders = Order::where('store_id', $storeId)
                    ->where('status', 'completed')
                    ->where(function ($q) use ($sessionStart, $sessionEnd) {
                        $q->whereBetween('completed_at', [$sessionStart, $sessionEnd])
                          ->orWhere(function ($sq) use ($sessionStart, $sessionEnd) {
                              $sq->whereNull('completed_at')
                                 ->whereBetween('created_at', [$sessionStart, $sessionEnd]);
                          });
                    })
                    ->get();

                $totalSales   = (float) round($sessionOrders->sum('total_amount'), 2);
                $totalOrders  = $sessionOrders->count();

                $totalRefunds = (float) round(
                    Order::where('store_id', $storeId)
                        ->where('status', 'refunded')
                        ->whereBetween('updated_at', [$sessionStart, $sessionEnd])
                        ->sum('total_amount'),
                    2
                );

                // Expected cash in drawer formula:
                // Opening Balance + Net Cash Sales - Cash Out - Verified Cash Refunds
                // Note: registers.cash_in is NOT added here; it represents session cash sales in reports.
                $openingBalance = (float) ($register->opening_balance ?? 0);
                $cashOut        = (float) ($register->cash_out ?? 0);

                $expectedCash = round($openingBalance + $netCashSales - $cashOut - $cashRefunds, 2);
                $actualCash   = $request->has('closing_balance') ? (float) round($request->get('closing_balance'), 2) : $expectedCash;
                $difference   = round($actualCash - $expectedCash, 2);

                // Update register, persisting net cash sales into cash_in for reporting compatibility
                $register->update([
                    'closing_balance'  => $actualCash,
                    'expected_balance' => $expectedCash,
                    'total_sales'      => $totalSales,
                    'total_refunds'    => $totalRefunds,
                    'cash_in'          => $netCashSales,
                    'status'           => 'closed',
                    'closed_at'        => $sessionEnd,
                    'notes'            => trim($request->get('notes', '') .
                        "\nSession Summary:" .
                        "\nTotal Orders: {$totalOrders}" .
                        "\nNet Cash Sales: Rs. " . number_format($netCashSales, 2) .
                        "\nGross Cash Tendered: Rs. " . number_format($grossCashPayments, 2) .
                        "\nChange Given: Rs. " . number_format($totalCashChange, 2) .
                        "\nCard: Rs. " . number_format($cardPayments, 2) .
                        "\nCash In (Net Cash Sales): Rs. " . number_format($netCashSales, 2) .
                        "\nCash Out: Rs. " . number_format($cashOut, 2) .
                        "\nCash Refunds: Rs. " . number_format($cashRefunds, 2) .
                        "\nExpected Cash: Rs. " . number_format($expectedCash, 2) .
                        "\nActual Cash: Rs. " . number_format($actualCash, 2) .
                        "\nDifference: Rs. " . number_format($difference, 2)),
                ]);

                // Calculate duration
                $duration = $sessionStart->diff($sessionEnd);
                $durationStr = '';
                if ($duration->h > 0) $durationStr .= $duration->h . 'h ';
                $durationStr .= $duration->i . 'm';

                return response()->json([
                    'success'  => true,
                    'register' => $register,
                    'summary'  => [
                        'total_orders'     => $totalOrders,
                        'total_sales'      => (float) $totalSales,
                        'gross_cash'       => (float) $grossCashPayments,
                        'cash_change'      => (float) $totalCashChange,
                        'net_cash_sales'   => (float) $netCashSales,
                        'cash_payments'    => (float) $netCashSales, // Preserved for UI backward-compatibility
                        'card_payments'    => (float) $cardPayments,
                        'opening_balance'  => (float) $openingBalance,
                        'cash_in'          => (float) $netCashSales,
                        'cash_out'         => (float) $cashOut,
                        'cash_refunds'     => (float) $cashRefunds,
                        'expected_cash'    => (float) $expectedCash,
                        'actual_cash'      => (float) $actualCash,
                        'difference'       => (float) $difference,
                        'session_duration' => $durationStr,
                    ],
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error closing register: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent sales history for POS modal.
     */
    public function salesHistory(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $limit = min((int) $request->get('limit', 50), 100);

        $query = Order::where('store_id', $storeId)
            ->with(['customer', 'items', 'payments'])
            ->latest();

        if ($request->filled('q')) {
            $q = trim($request->get('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('order_number', 'like', "%{$q}%")
                    ->orWhere('uuid', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($c) use ($q) {
                        $c->where('name', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%");
                    });
            });
        }

        $orders = $query->take($limit)->get();

        $formatted = $orders->map(function ($o) {
            return [
                'id'              => $o->id,
                'uuid'            => $o->uuid,
                'order_number'    => $o->order_number,
                'order_type'      => $o->order_type ?? 'sale',
                'status'          => $o->status,
                'sync_status'     => 'synced',
                'customer_id'     => $o->customer_id,
                'customer_name'   => $o->customer?->name ?? 'Walk-in Customer',
                'customer_phone'  => $o->customer?->phone,
                'customer_email'  => $o->customer?->email,
                'subtotal'        => (float) ($o->subtotal ?: $o->total_amount),
                'tax_amount'      => (float) $o->tax_amount,
                'discount_amount' => (float) $o->discount_amount,
                'total_amount'    => (float) $o->total_amount,
                'paid_amount'     => (float) $o->paid_amount,
                'change_amount'   => (float) $o->change_amount,
                'created_at'      => $o->created_at ? $o->created_at->toIso8601String() : now()->toIso8601String(),
                'items'           => $o->items->map(function ($it) {
                    return [
                        'id'              => $it->id,
                        'product_id'      => $it->product_id,
                        'name'            => $it->product_name,
                        'product_name'    => $it->product_name,
                        'sku'             => $it->sku,
                        'barcode'         => $it->barcode,
                        'quantity'        => (float) $it->quantity,
                        'price'           => (float) $it->unit_price,
                        'unit_price'      => (float) $it->unit_price,
                        'discount_amount' => (float) $it->discount_amount,
                        'tax'             => (float) $it->tax_amount,
                        'total'           => (float) $it->total,
                    ];
                }),
                'payments'        => $o->payments->map(function ($p) {
                    return [
                        'method'    => $p->method,
                        'amount'    => (float) $p->amount,
                        'reference' => $p->reference_number,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'orders'  => $formatted,
        ]);
    }

    /**
     * Get daily sales summary report for POS modal.
     */
    public function dailyReport(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $date = $request->get('date', now()->toDateString());

        $orders = Order::where('store_id', $storeId)
            ->whereDate('created_at', $date)
            ->where('status', '!=', 'cancelled')
            ->with(['payments', 'items'])
            ->get();

        $grossSales = 0.0;
        $totalDiscounts = 0.0;
        $totalTax = 0.0;
        $returnsAmount = 0.0;
        $orderCount = 0;
        $returnsCount = 0;
        $payments = [
            'cash'          => 0.0,
            'card'          => 0.0,
            'mobile'        => 0.0,
            'bank_transfer' => 0.0,
            'other'         => 0.0,
        ];

        foreach ($orders as $order) {
            if ($order->order_type === 'return' || $order->status === 'refunded') {
                $returnsCount++;
                $returnsAmount += (float) $order->total_amount;
            } else {
                $orderCount++;
                $grossSales += (float) ($order->subtotal ?: $order->total_amount);
                $totalDiscounts += (float) $order->discount_amount;
                $totalTax += (float) $order->tax_amount;

                $changeRemaining = max(0.0, (float) ($order->change_amount ?? 0));
                foreach ($order->payments as $p) {
                    $amt = (float) $p->amount;
                    if ($p->method === 'cash' && $changeRemaining > 0) {
                        $deduct = min($amt, $changeRemaining);
                        $amt -= $deduct;
                        $changeRemaining -= $deduct;
                    }

                    $methodKey = match ($p->method) {
                        'cash'                           => 'cash',
                        'card'                           => 'card',
                        'mobile', 'mobile_payment', 'qr' => 'mobile',
                        'bank_transfer', 'bank'          => 'bank_transfer',
                        default                          => 'other',
                    };
                    $payments[$methodKey] += $amt;
                }
            }
        }

        $netSales = max(0.0, $grossSales - $totalDiscounts + $totalTax - $returnsAmount);

        return response()->json([
            'success'        => true,
            'date'           => $date,
            'total_orders'   => $orderCount,
            'total_returns'  => $returnsCount,
            'gross_sales'    => round($grossSales, 2),
            'discounts'      => round($totalDiscounts, 2),
            'tax'            => round($totalTax, 2),
            'returns_amount' => round($returnsAmount, 2),
            'net_sales'      => round($netSales, 2),
            'payments'       => [
                'cash'           => round($payments['cash'], 2),
                'card'           => round($payments['card'], 2),
                'mobile'         => round($payments['mobile'], 2),
                'mobile_payment' => round($payments['mobile'], 2),
                'bank_transfer'  => round($payments['bank_transfer'], 2),
                'other'          => round($payments['other'], 2),
            ],
        ]);
    }
}
