<?php
namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Str;

class InventoryService
{
    /**
     * Log an inventory movement.
     * Enforces UUID assignment and full audit details.
     */
    public function logMovement(array $data): InventoryMovement
    {
        if (empty($data['uuid'])) {
            $data['uuid'] = Str::uuid()->toString();
        }
        return InventoryMovement::create($data);
    }

    /**
     * Adjust stock for a product manually.
     * Tracks before and after quantities, device, reference UUID, and negative stock conflicts.
     */
    public function adjustStock(
        Product $product,
        float $quantity,
        string $type,
        string $notes = '',
        ?int $userId = null,
        ?string $deviceId = null,
        ?string $movementUuid = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $referenceUuid = null
    ): InventoryMovement {
        $before = (float) $product->stock_quantity;
        $rawAfter = $before + $quantity;
        $hasConflict = false;

        if ($rawAfter < 0 && !$product->allow_negative_stock) {
            $hasConflict = true;
            $conflictMsg = "Stock adjustment resulted in negative balance ({$rawAfter}) without negative stock allowed.";
            $notes = $notes ? ($notes . ' | ' . $conflictMsg) : $conflictMsg;
            $after = 0;
        } else {
            $after = $rawAfter;
        }

        // Updating via Eloquent model updates sync_version via model saving event
        $product->update(['stock_quantity' => $after]);

        return $this->logMovement([
            'uuid'            => $movementUuid ?? Str::uuid()->toString(),
            'store_id'        => $product->store_id,
            'product_id'      => $product->id,
            'user_id'         => $userId ?? auth()->id(),
            'device_id'       => $deviceId,
            'sync_status'     => $hasConflict ? 'conflict' : 'synced',
            'type'            => $type,
            'reference_type'  => $referenceType,
            'reference_id'    => $referenceId,
            'reference_uuid'  => $referenceUuid,
            'quantity'        => $quantity,
            'quantity_before' => $before,
            'quantity_after'  => $after,
            'unit_cost'       => $product->cost_price,
            'total_cost'      => abs($product->cost_price * $quantity),
            'notes'           => $notes,
        ]);
    }

    /**
     * Get low stock products for a store.
     */
    public function getLowStockProducts(int $storeId): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('store_id', $storeId)
            ->where('track_stock', true)
            ->where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->with('category')
            ->orderBy('stock_quantity', 'asc')
            ->get();
    }
}
