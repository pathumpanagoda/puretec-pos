<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InventoryMovement extends Model
{
    protected $fillable = [
        'uuid','store_id','product_id','user_id','device_id','sync_status','type',
        'reference_type','reference_id','reference_uuid',
        'quantity','quantity_before','quantity_after','unit_cost','total_cost','notes'
    ];
    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($movement) {
            if (empty($movement->uuid)) {
                $movement->uuid = Str::uuid()->toString();
            }
        });
    }

    public function store(): BelongsTo   { return $this->belongsTo(Store::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
}
