<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'store_id','supplier_id','user_id','po_number','status','order_date',
        'expected_date','received_date','subtotal','tax_amount','discount_amount',
        'shipping_cost','total_amount','paid_amount','due_amount','reference_number','notes'
    ];
    protected $casts = ['order_date' => 'date', 'expected_date' => 'date', 'received_date' => 'date'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($po) {
            if (empty($po->po_number)) {
                $po->po_number = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }

    public function store(): BelongsTo    { return $this->belongsTo(Store::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function items(): HasMany      { return $this->hasMany(PurchaseOrderItem::class); }
}
