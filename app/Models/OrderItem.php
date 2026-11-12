<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderItem extends Model
{
    protected $fillable = [
        'uuid', 'order_id', 'product_id', 'product_variant_id', 'product_name', 'sku', 'barcode',
        'quantity', 'unit', 'unit_price', 'cost_price', 'discount_amount', 'tax_amount',
        'tax_rate', 'subtotal', 'total', 'profit', 'notes', 'addons'
    ];

    protected $casts = [
        'addons' => 'array',
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($item) {
            if (empty($item->uuid)) {
                $item->uuid = Str::uuid()->toString();
            }
        });
    }

    public function order(): BelongsTo   { return $this->belongsTo(Order::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
