<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            'track_stock', 'allow_negative_stock', 'unit', 'sync_version', 'updated_at'
        ])->paginate($request->per_page ?? 100);

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }

    /**
     * Get stock movements.
     * Supports filtering by product, type, sync_status (e.g. 'conflict'), and date.
     *
     * GET /api/v1/stock-movements?product_id=1&type=sale&sync_status=conflict&since=...
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
        if ($request->sync_status) {
            $query->where('sync_status', $request->sync_status);
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

    /**
     * Record a stock movement online via REST API.
     * Enforces UUID idempotency, pessimistic row locking, conflict detection, and audit trail.
     *
     * POST /api/v1/stock-movements
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id'     => 'required|exists:products,id',
            'quantity'       => 'required|numeric|not_in:0',
            'type'           => 'required|in:adjustment,damage,expired,return,transfer,initial,purchase',
            'notes'          => 'nullable|string|max:500',
            'uuid'           => 'nullable|uuid',
            'reference_type' => 'nullable|string|max:64',
            'reference_id'   => 'nullable|integer',
            'reference_uuid' => 'nullable|uuid',
        ]);

        $user = $request->user();
        $uuid = $request->uuid ?? Str::uuid()->toString();

        // Idempotency check
        $existing = InventoryMovement::where('uuid', $uuid)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'status'  => 'duplicate',
                'message' => 'Movement already recorded on server.',
                'data'    => $existing,
            ], 200);
        }

        $deviceId = $request->header('X-Device-ID') ?? $request->input('device_id');

        return DB::transaction(function () use ($request, $user, $uuid, $deviceId) {
            $product = Product::where('store_id', $user->store_id)
                ->where('id', $request->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            $before = (float) $product->stock_quantity;
            $quantity = (float) $request->quantity;
            $rawAfter = $before + $quantity;
            $hasConflict = false;
            $conflictMsg = null;

            if ($rawAfter < 0 && !$product->allow_negative_stock) {
                $hasConflict = true;
                $conflictMsg = "Adjustment caused negative stock on '{$product->name}' (Stock before: {$before}, Change: {$quantity}, Result: {$rawAfter})";
                $after = $product->allow_negative_stock ? $rawAfter : max(0, $rawAfter);
            } else {
                $after = $rawAfter;
            }

            if ($product->track_stock) {
                $product->update(['stock_quantity' => $after]);
            }

            $movement = $this->inventoryService->logMovement([
                'uuid'            => $uuid,
                'store_id'        => $user->store_id,
                'product_id'      => $product->id,
                'user_id'         => $user->id,
                'device_id'       => $deviceId,
                'sync_status'     => $hasConflict ? 'conflict' : 'synced',
                'type'            => $request->type,
                'reference_type'  => $request->reference_type,
                'reference_id'    => $request->reference_id,
                'reference_uuid'  => $request->reference_uuid,
                'quantity'        => $quantity,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'unit_cost'       => $product->cost_price,
                'total_cost'      => abs($product->cost_price * $quantity),
                'notes'           => $hasConflict ? ($request->notes ? $request->notes . ' | ' . $conflictMsg : $conflictMsg) : $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'status'  => $hasConflict ? 'conflict' : 'synced',
                'message' => $hasConflict ? $conflictMsg : 'Stock movement recorded successfully.',
                'data'    => $movement,
            ], $hasConflict ? 409 : 201);
        });
    }
}
