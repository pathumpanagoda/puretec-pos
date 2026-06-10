<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'store_id','customer_group_id','name','email','phone','address','city',
        'nic','date_of_birth','credit_limit','current_balance','total_purchases',
        'total_orders','loyalty_points','loyalty_tier','notes','is_active'
    ];
    protected $casts = [
        'is_active'      => 'boolean',
        'date_of_birth'  => 'date',
        'credit_limit'   => 'decimal:2',
        'current_balance'=> 'decimal:2',
        'total_purchases'=> 'decimal:2',
    ];

    public function store(): BelongsTo         { return $this->belongsTo(Store::class); }
    public function group(): BelongsTo         { return $this->belongsTo(CustomerGroup::class, 'customer_group_id'); }
    public function orders(): HasMany          { return $this->hasMany(Order::class); }
    public function giftCards(): HasMany       { return $this->hasMany(GiftCard::class); }

    public function scopeSearch($query, $term) {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
        });
    }
}
