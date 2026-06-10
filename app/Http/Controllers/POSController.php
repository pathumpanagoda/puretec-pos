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
            ->get(['id','name','phone','email','loyalty_points','current_balance']);
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
        try {
            $storeId = Auth::user()->store_id;
            $sessionStart = $register->opened_at ?? now()->subHours(8);
            $sessionEnd = now();

            // Calculate sales during this session (from opened_at to now)
            $totalSales = Order::where('store_id', $storeId)
                ->where('status', 'completed')
                ->where('created_at', '>=', $sessionStart)
                ->where('created_at', '<=', $sessionEnd)
                ->sum('total_amount');

            $totalOrders = Order::where('store_id', $storeId)
                ->where('status', 'completed')
                ->where('created_at', '>=', $sessionStart)
                ->where('created_at', '<=', $sessionEnd)
                ->count();

            // Calculate cash payments during session
            $cashPayments = \App\Models\Payment::whereHas('order', fn($q) =>
                $q->where('store_id', $storeId)
                  ->where('status', 'completed')
                  ->where('created_at', '>=', $sessionStart)
                  ->where('created_at', '<=', $sessionEnd)
            )->where('method', 'cash')->sum('amount') ?? 0;

            // Calculate card payments during session
            $cardPayments = \App\Models\Payment::whereHas('order', fn($q) =>
                $q->where('store_id', $storeId)
                  ->where('status', 'completed')
                  ->where('created_at', '>=', $sessionStart)
                  ->where('created_at', '<=', $sessionEnd)
            )->where('method', 'card')->sum('amount') ?? 0;

            // Calculate refunds during session
            $totalRefunds = Order::where('store_id', $storeId)
                ->where('status', 'refunded')
                ->where('updated_at', '>=', $sessionStart)
                ->where('updated_at', '<=', $sessionEnd)
                ->sum('total_amount') ?? 0;

            $expectedCash = ($register->opening_balance ?? 0) + $cashPayments - ($register->cash_out ?? 0);
            $actualCash = (float) $request->get('closing_balance', $expectedCash);
            $difference = $actualCash - $expectedCash;

            $register->update([
                'closing_balance'  => $actualCash,
                'expected_balance' => $expectedCash,
                'total_sales'      => $totalSales,
                'total_refunds'    => $totalRefunds,
                'cash_in'          => $cashPayments,
                'status'           => 'closed',
                'closed_at'        => $sessionEnd,
                'notes'            => trim($request->get('notes', '') .
                    "\nSession Summary:" .
                    "\nTotal Orders: {$totalOrders}" .
                    "\nCash: Rs. " . number_format($cashPayments, 2) .
                    "\nCard: Rs. " . number_format($cardPayments, 2) .
                    "\nDifference: Rs. " . number_format($difference, 2)),
            ]);

            // Calculate duration
            $duration = $sessionStart->diff($sessionEnd);
            $durationStr = '';
            if ($duration->h > 0) $durationStr .= $duration->h . 'h ';
            $durationStr .= $duration->i . 'm';

            return response()->json([
                'success' => true,
                'register' => $register,
                'summary' => [
                    'total_orders' => $totalOrders,
                    'total_sales' => (float) $totalSales,
                    'cash_payments' => (float) $cashPayments,
                    'card_payments' => (float) $cardPayments,
                    'expected_cash' => (float) $expectedCash,
                    'actual_cash' => (float) $actualCash,
                    'difference' => (float) $difference,
                    'session_duration' => $durationStr,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error closing register: ' . $e->getMessage()
            ], 500);
        }
    }
}
