<?php
namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;

class InventoryService
{
    /**
     * Log an inventory movement.
     */
    public function logMovement(array $data): InventoryMovement
    {
        return InventoryMovement::create($data);
    }

    /**
     * Adjust stock for a product manually.
     */
    public function adjustStock(Product $product, float $quantity, string $type, string $notes = '', int $userId = null): InventoryMovement
    {
        $before = $product->stock_quantity;
        $after  = $before + $quantity;

        $product->update(['stock_quantity' => max(0, $after)]);

        return $this->logMovement([
            'store_id'       => $product->store_id,
            'product_id'     => $product->id,
            'user_id'        => $userId ?? auth()->id(),
            'type'           => $type,
            'quantity'       => $quantity,
            'quantity_before'=> $before,
            'quantity_after' => max(0, $after),
            'unit_cost'      => $product->cost_price,
            'total_cost'     => abs($product->cost_price * $quantity),
            'notes'          => $notes,
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
