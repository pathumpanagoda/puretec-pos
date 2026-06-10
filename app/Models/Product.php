<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'store_id','category_id','supplier_id','tax_id','name','sku','barcode',
        'description','image','gallery','product_type','cost_price','selling_price',
        'wholesale_price','min_selling_price','stock_quantity','min_stock','max_stock',
        'reorder_level','unit','weight','track_stock','allow_negative_stock',
        'has_variants','is_featured','is_active','is_taxable','expiry_date','location','notes'
    ];
    protected $casts = [
        'gallery'            => 'array',
        'is_active'          => 'boolean',
        'is_featured'        => 'boolean',
        'is_taxable'         => 'boolean',
        'track_stock'        => 'boolean',
        'allow_negative_stock'=> 'boolean',
        'has_variants'       => 'boolean',
        'cost_price'         => 'decimal:2',
        'selling_price'      => 'decimal:2',
        'stock_quantity'     => 'decimal:3',
        'expiry_date'        => 'date',
    ];

    public function store(): BelongsTo              { return $this->belongsTo(Store::class); }
    public function category(): BelongsTo           { return $this->belongsTo(Category::class); }
    public function supplier(): BelongsTo           { return $this->belongsTo(Supplier::class); }
    public function tax(): BelongsTo                { return $this->belongsTo(Tax::class); }
    public function variants(): HasMany             { return $this->hasMany(ProductVariant::class); }
    public function orderItems(): HasMany           { return $this->hasMany(OrderItem::class); }
    public function inventoryMovements(): HasMany   { return $this->hasMany(InventoryMovement::class); }

    public function isLowStock(): bool  { return $this->stock_quantity <= $this->min_stock && $this->track_stock; }
    public function isOutOfStock(): bool{ return $this->stock_quantity <= 0 && $this->track_stock; }

    public function getProfitMarginAttribute(): float
    {
        if ($this->selling_price <= 0) return 0;
        return round((($this->selling_price - $this->cost_price) / $this->selling_price) * 100, 2);
    }

    public function scopeActive($query)    { return $query->where('is_active', true); }
    public function scopeLowStock($query)  { return $query->whereColumn('stock_quantity', '<=', 'min_stock')->where('track_stock', true); }
    public function scopeSearch($query, $term) {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%")
              ->orWhere('selling_price', 'like', "%{$term}%")
              ->orWhereHas('category', function($catQ) use ($term) {
                  $catQ->where('name', 'like', "%{$term}%");
              });
        });
    }
}
