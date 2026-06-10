<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCard extends Model
{
    protected $fillable = ['store_id','code','initial_amount','current_balance','customer_id','expiry_date','status'];
    protected $casts    = ['expiry_date' => 'date', 'initial_amount' => 'decimal:2', 'current_balance' => 'decimal:2'];
    public function store(): BelongsTo    { return $this->belongsTo(Store::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function isExpired(): bool     { return $this->expiry_date && $this->expiry_date->isPast(); }
    public function isActive(): bool      { return $this->status === 'active' && !$this->isExpired(); }
}
