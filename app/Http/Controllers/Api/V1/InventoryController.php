<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get inventory (products with stock info).
     *
     * GET /api/v1/inventory?low_stock=true&since=...
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Product::where('store_id', $user->store_id)
            ->where('is_active', true)
            ->where('track_stock', true);

        if ($request->low_stock) {
            $query->whereColumn('stock_quantity', '<=', 'min_stock');
        }

        if ($request->since) {
            $query->where('updated_at', '>', $request->since);
        }

        $products = $query->select([
            'id', 'uuid', 'name', 'sku', 'barcode',
            'stock_quantity', 'min_stock', 'cost_price',
            'track_stock', 'unit', 'updated_at'
        ])->paginate($request->per_page ?? 100);

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }

    /**
     * Get stock movements.
     *
     * GET /api/v1/stock-movements?product_id=1&type=sale&since=...
     */
    public function movements(Request $request)
    {
        $user = $request->user();
        $query = InventoryMovement::where('store_id', $user->store_id)
            ->with(['product:id,name,sku', 'user:id,name']);

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->since) {
            $query->where('created_at', '>', $request->since);
        }

        $movements = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json([
            'success' => true,
            'data'    => $movements,
        ]);
    }
}
