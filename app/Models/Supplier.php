<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'store_id','name','company','email','phone','mobile',
        'address','city','country','tax_number','opening_balance',
        'current_balance','notes','is_active'
    ];
    protected $casts = ['is_active' => 'boolean', 'opening_balance' => 'decimal:2', 'current_balance' => 'decimal:2'];
    public function store(): BelongsTo           { return $this->belongsTo(Store::class); }
    public function products(): HasMany          { return $this->hasMany(Product::class); }
    public function purchaseOrders(): HasMany    { return $this->hasMany(PurchaseOrder::class); }
}
