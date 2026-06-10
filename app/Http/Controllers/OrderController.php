<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Payment;
use App\Services\POSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(private POSService $posService) {}

    public function index(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $query   = Order::where('store_id', $storeId)->with('customer','user');
        if ($request->filled('search'))  $query->where('order_number', 'like', "%{$request->search}%");
        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('from'))    $query->whereDate('created_at', '>=', $request->from);
        if ($request->filled('to'))      $query->whereDate('created_at', '<=', $request->to);
        $orders = $query->latest()->paginate(20)->withQueryString();
        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.product','payments','customer','user');
        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load('items.product', 'payments', 'customer', 'user');
        $storeId = Auth::user()->store_id;
        $customers = Customer::where('store_id', $storeId)->where('is_active', true)->orderBy('name')->get();
        $products = Product::where('store_id', $storeId)->where('is_active', true)->orderBy('name')->get();
        return view('orders.edit', compact('order', 'customers', 'products'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Update order basic info
            $order->customer_id = $request->customer_id;
            $order->discount_type = $request->discount_type;
            $order->discount_value = $request->discount_value ?? 0;
            $order->notes = $request->notes;

            // Delete existing items
            $order->items()->delete();

            // Calculate new totals
            $subtotal = 0;
            $totalProfit = 0;

            // Add new items
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                $costPrice = $product->cost_price ?? 0;
                $itemTotal = $quantity * $unitPrice;
                $itemProfit = ($unitPrice - $costPrice) * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'quantity' => $quantity,
                    'unit' => $product->unit ?? 'pcs',
                    'unit_price' => $unitPrice,
                    'cost_price' => $costPrice,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'tax_rate' => 0,
                    'subtotal' => $itemTotal,
                    'total' => $itemTotal,
                    'profit' => $itemProfit,
                ]);

                $subtotal += $itemTotal;
                $totalProfit += $itemProfit;
            }

            // Calculate discount
            $discountAmount = 0;
            if ($order->discount_type === 'percentage' && $order->discount_value > 0) {
                $discountAmount = $subtotal * ($order->discount_value / 100);
            } elseif ($order->discount_type === 'fixed' && $order->discount_value > 0) {
                $discountAmount = $order->discount_value;
            }

            // Calculate tax
            $store = Auth::user()->store;
            $taxRate = $store->tax_rate ?? 0;
            $taxableAmount = $subtotal - $discountAmount;
            $taxAmount = $taxableAmount * ($taxRate / 100);

            // Update order totals
            $order->subtotal = $subtotal;
            $order->discount_amount = $discountAmount;
            $order->tax_amount = $taxAmount;
            $order->total_amount = $subtotal - $discountAmount + $taxAmount;
            $order->profit = $totalProfit - $discountAmount;

            // Recalculate payment status
            $paidAmount = $order->payments()->sum('amount');
            $order->paid_amount = $paidAmount;
            $order->due_amount = max(0, $order->total_amount - $paidAmount);
            $order->change_amount = max(0, $paidAmount - $order->total_amount);

            $order->save();

            DB::commit();
            return redirect()->route('orders.show', $order)->with('success', 'Order updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update order: ' . $e->getMessage())->withInput();
        }
    }

    public function printReceipt(Order $order)
    {
        $order->load('items','payments','customer','user','store');
        return view('orders.receipt', compact('order'));
    }

    public function printInvoice(Request $request, Order $order)
    {
        $order->load('items','payments','customer','user','store');
        $size = $request->get('size', 'a4');
        return view('orders.invoice', compact('order', 'size'));
    }

    public function refund(Request $request, Order $order)
    {
        $request->validate(['items' => 'required|array', 'reason' => 'nullable|string']);
        try {
            $refunded = $this->posService->processRefund($order, $request->items, $request->reason ?? '');
            return response()->json(['success' => true, 'order' => $refunded]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Order $order)
    {
        if ($order->status === 'completed') return back()->with('error', 'Cannot delete a completed order. Use refund instead.');
        $order->delete();
        return back()->with('success', 'Order deleted!');
    }

    /**
     * Bulk delete orders (super_admin only)
     */
    public function bulkDelete(Request $request)
    {
        $user = Auth::user();

        // Only super_admin can bulk delete
        if ($user->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only Super Admin can perform bulk delete.'
            ], 403);
        }

        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'integer|exists:orders,id'
        ]);

        $storeId = $user->store_id;
        $orderIds = $request->order_ids;

        DB::beginTransaction();
        try {
            // Only delete orders belonging to this store
            $orders = Order::where('store_id', $storeId)
                          ->whereIn('id', $orderIds)
                          ->get();

            $deletedCount = 0;
            foreach ($orders as $order) {
                // Restore stock for completed orders
                if ($order->status === 'completed') {
                    foreach ($order->items as $item) {
                        if ($item->product) {
                            $item->product->increment('stock_quantity', $item->quantity);
                        }
                    }
                }

                // Delete related records
                $order->items()->delete();
                $order->payments()->delete();
                $order->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} orders deleted successfully.",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete orders: ' . $e->getMessage()
            ], 500);
        }
    }
}
