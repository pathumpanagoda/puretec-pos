<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    protected $fillable = ['store_id','name','discount_rate','min_purchase','is_active'];
    protected $casts    = ['is_active' => 'boolean', 'discount_rate' => 'decimal:2'];
    public function store(): BelongsTo      { return $this->belongsTo(Store::class); }
    public function customers(): HasMany    { return $this->hasMany(Customer::class); }
}
