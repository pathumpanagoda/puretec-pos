<?php
namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $storeId   = Auth::user()->store_id;
        $query     = PurchaseOrder::where('store_id', $storeId)->with('supplier','user');
        if ($request->filled('status')) $query->where('status', $request->status);
        $purchases = $query->latest()->paginate(20)->withQueryString();
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $storeId   = Auth::user()->store_id;
        $suppliers = Supplier::where('store_id', $storeId)->where('is_active', true)->get();
        $products  = Product::where('store_id', $storeId)->where('is_active', true)->get(['id','name','sku','cost_price','unit']);
        return view('purchases.create', compact('suppliers','products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'order_date'  => 'required|date',
            'items'       => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $storeId  = Auth::user()->store_id;
            $subtotal = 0;
            $total    = 0;

            foreach ($request->items as $item) {
                $itemTotal = $item['ordered_quantity'] * $item['unit_cost'];
                $subtotal += $itemTotal;
                $total    += $itemTotal;
            }

            $po = PurchaseOrder::create([
                'store_id'    => $storeId,
                'supplier_id' => $request->supplier_id,
                'user_id'     => Auth::id(),
                'order_date'  => $request->order_date,
                'expected_date'=> $request->expected_date,
                'subtotal'    => $subtotal,
                'total_amount'=> $total,
                'notes'       => $request->notes,
                'status'      => $request->status ?? 'ordered',
            ]);

            foreach ($request->items as $item) {
                $itemTotal = $item['ordered_quantity'] * $item['unit_cost'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $item['product_id'],
                    'product_name'      => Product::find($item['product_id'])?->name ?? 'Unknown',
                    'ordered_quantity'  => $item['ordered_quantity'],
                    'received_quantity' => 0,
                    'unit'              => $item['unit'] ?? 'pcs',
                    'unit_cost'         => $item['unit_cost'],
                    'subtotal'          => $itemTotal,
                    'total'             => $itemTotal,
                ]);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase order created!');
    }

    public function show(PurchaseOrder $purchase)
    {
        $purchase->load('items.product','supplier','user');
        return view('purchases.show', compact('purchase'));
    }

    public function receive(Request $request, PurchaseOrder $purchase)
    {
        DB::transaction(function () use ($request, $purchase) {
            $storeId = Auth::user()->store_id;
            $allReceived = true;

            foreach ($request->items as $itemData) {
                $item = PurchaseOrderItem::findOrFail($itemData['id']);
                $receivedQty = (float) $itemData['received_quantity'];
                $item->update(['received_quantity' => $item->received_quantity + $receivedQty]);

                if ($item->received_quantity < $item->ordered_quantity) $allReceived = false;

                // Update product stock
                if ($receivedQty > 0) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $before = $product->stock_quantity;
                        $product->increment('stock_quantity', $receivedQty);
                        if ($request->update_cost && $item->unit_cost > 0) {
                            $product->update(['cost_price' => $item->unit_cost]);
                        }
                        $this->inventoryService->logMovement([
                            'store_id'       => $storeId,
                            'product_id'     => $product->id,
                            'user_id'        => Auth::id(),
                            'type'           => 'purchase',
                            'reference_type' => 'purchase_order',
                            'reference_id'   => $purchase->id,
                            'quantity'       => $receivedQty,
                            'quantity_before'=> $before,
                            'quantity_after' => $before + $receivedQty,
                            'unit_cost'      => $item->unit_cost,
                            'total_cost'     => $item->unit_cost * $receivedQty,
                        ]);
                    }
                }
            }

            $purchase->update([
                'status'        => $allReceived ? 'received' : 'partial',
                'received_date' => now()->toDateString(),
            ]);
        });

        return back()->with('success', 'Stock received successfully!');
    }
}
