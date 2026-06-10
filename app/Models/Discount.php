<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discount extends Model
{
    protected $fillable = [
        'store_id','name','code','type','value','min_purchase','max_discount',
        'usage_limit','usage_count','start_date','end_date','is_active'
    ];
    protected $casts = ['is_active' => 'boolean', 'start_date' => 'date', 'end_date' => 'date', 'value' => 'decimal:2'];
    public function store(): BelongsTo { return $this->belongsTo(Store::class); }
    public function isValid(): bool    { return $this->is_active && (!$this->end_date || !$this->end_date->isPast()) && (!$this->usage_limit || $this->usage_count < $this->usage_limit); }
}
