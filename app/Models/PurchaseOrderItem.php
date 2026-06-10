<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id','product_id','product_name','ordered_quantity',
        'received_quantity','unit','unit_cost','tax_rate','tax_amount',
        'discount_amount','subtotal','total','expiry_date','batch_number'
    ];
    protected $casts = ['expiry_date' => 'date'];
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function product(): BelongsTo       { return $this->belongsTo(Product::class); }
}
