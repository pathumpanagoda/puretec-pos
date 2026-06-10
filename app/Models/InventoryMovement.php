<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'store_id','product_id','user_id','type','reference_type','reference_id',
        'quantity','quantity_before','quantity_after','unit_cost','total_cost','notes'
    ];
    protected $casts = ['quantity' => 'decimal:3', 'unit_cost' => 'decimal:2'];
    public function store(): BelongsTo   { return $this->belongsTo(Store::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
}
