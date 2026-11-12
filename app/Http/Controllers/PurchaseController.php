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
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'supplier_id' => [
                'nullable',
                Rule::exists('suppliers', 'id')->where(function ($query) {
                    $query->where('store_id', Auth::user()->store_id);
                }),
            ],
            'order_date'                => 'required|date',
            'expected_date'             => 'nullable|date',
            'notes'                     => 'nullable|string|max:65535',
            'status'                    => 'nullable|string|in:draft,ordered,partial,received,cancelled',
            'items'                     => 'required|array|min:1',
            'items.*.product_id'        => [
                'required',
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->where('store_id', Auth::user()->store_id);
                }),
            ],
            'items.*.ordered_quantity'  => 'required|numeric|gt:0',
            'items.*.unit_cost'         => 'required|numeric|min:0',
            'items.*.unit'              => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($request) {
            $storeId  = Auth::user()->store_id;
            $subtotal = 0;
            $total    = 0;

            foreach ($request->items as $item) {
                $itemTotal = (float) $item['ordered_quantity'] * (float) $item['unit_cost'];
                $subtotal += $itemTotal;
                $total    += $itemTotal;
            }

            $po = PurchaseOrder::create([
                'store_id'      => $storeId,
                'supplier_id'   => $request->supplier_id,
                'user_id'       => Auth::id(),
                'order_date'    => $request->order_date,
                'expected_date' => $request->expected_date,
                'subtotal'      => $subtotal,
                'total_amount'  => $total,
                'notes'         => $request->notes,
                'status'        => $request->status ?? 'ordered',
            ]);

            foreach ($request->items as $item) {
                $product = Product::where('store_id', $storeId)->find($item['product_id']);
                if (!$product) {
                    abort(404);
                }

                $itemTotal = (float) $item['ordered_quantity'] * (float) $item['unit_cost'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $product->id,
                    'product_name'      => $product->name,
                    'ordered_quantity'  => $item['ordered_quantity'],
                    'received_quantity' => 0,
                    'unit'              => $item['unit'] ?? $product->unit ?? 'pcs',
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
        if ((int) $purchase->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

        $purchase->load('items.product','supplier','user');
        return view('purchases.show', compact('purchase'));
    }

    public function receive(Request $request, PurchaseOrder $purchase)
    {
        if ((int) $purchase->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

        $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.id'                => [
                'required',
                'distinct',
                Rule::exists('purchase_order_items', 'id')->where(function ($query) use ($purchase) {
                    $query->where('purchase_order_id', $purchase->id);
                }),
            ],
            'items.*.received_quantity' => 'required|numeric|min:0',
            'update_cost'               => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $purchase) {
            $storeId = Auth::user()->store_id;
            $hasAnyReceived = false;

            foreach ($request->items as $index => $itemData) {
                $item = $purchase->items()->where('id', $itemData['id'])->firstOrFail();
                $receivedQty = (float) $itemData['received_quantity'];
                $remaining = (float) $item->ordered_quantity - (float) $item->received_quantity;

                if ($receivedQty > $remaining) {
                    throw ValidationException::withMessages([
                        "items.{$index}.received_quantity" => [
                            "Received quantity cannot exceed remaining ordered quantity for {$item->product_name}."
                        ],
                    ]);
                }

                if ($receivedQty > 0) {
                    $hasAnyReceived = true;
                    $item->update(['received_quantity' => $item->received_quantity + $receivedQty]);

                    $product = Product::where('store_id', $storeId)->find($item->product_id);
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

            $allItems = $purchase->items()->get();
            $allReceived = $allItems->count() > 0 && $allItems->every(function ($item) {
                return (float) $item->received_quantity >= (float) $item->ordered_quantity;
            });
            $anyReceived = $allItems->contains(function ($item) {
                return (float) $item->received_quantity > 0;
            });

            if ($allReceived) {
                $purchase->update([
                    'status'        => 'received',
                    'received_date' => now()->toDateString(),
                ]);
            } elseif ($anyReceived) {
                $purchase->update([
                    'status'        => 'partial',
                    'received_date' => $purchase->received_date ?? now()->toDateString(),
                ]);
            }
        });

        return back()->with('success', 'Stock received successfully!');
    }
}
