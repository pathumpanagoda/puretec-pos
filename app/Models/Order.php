<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'store_id','customer_id','user_id','order_number','status','order_type',
        'subtotal','tax_amount','discount_amount','shipping_amount','total_amount',
        'paid_amount','change_amount','due_amount','profit','discount_type',
        'discount_value','coupon_code','table_number','notes','internal_notes',
        'loyalty_points_earned','loyalty_points_used','completed_at',
        'billed_by','payment_processed_by','billed_at','payment_approved_at'
    ];
    protected $casts = [
        'completed_at'        => 'datetime',
        'billed_at'           => 'datetime',
        'payment_approved_at' => 'datetime',
        'subtotal'            => 'decimal:2',
        'total_amount'        => 'decimal:2',
        'paid_amount'         => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }

    public function store(): BelongsTo    { return $this->belongsTo(Store::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function items(): HasMany      { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany   { return $this->hasMany(Payment::class); }
    public function billedBy(): BelongsTo { return $this->belongsTo(User::class, 'billed_by'); }
    public function paymentProcessedBy(): BelongsTo { return $this->belongsTo(User::class, 'payment_processed_by'); }

    public function isCompleted(): bool       { return $this->status === 'completed'; }
    public function isCancelled(): bool       { return $this->status === 'cancelled'; }
    public function isRefunded(): bool        { return $this->status === 'refunded'; }
    public function isPendingPayment(): bool  { return $this->status === 'pending_payment'; }

    public function scopeCompleted($query)      { return $query->where('status', 'completed'); }
    public function scopePendingPayment($query) { return $query->where('status', 'pending_payment'); }
    public function scopeToday($query)          { return $query->whereDate('created_at', today()); }
}
