<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['product_id','name','sku','barcode','attributes','cost_price','selling_price','stock_quantity','image','is_active'];
    protected $casts    = ['attributes' => 'array', 'is_active' => 'boolean', 'cost_price' => 'decimal:2', 'selling_price' => 'decimal:2', 'stock_quantity' => 'decimal:3'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
