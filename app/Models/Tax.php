<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tax extends Model
{
    protected $fillable = ['store_id','name','rate','type','is_inclusive','is_active'];
    protected $casts    = ['rate' => 'decimal:2', 'is_inclusive' => 'boolean', 'is_active' => 'boolean'];
    public function store(): BelongsTo { return $this->belongsTo(Store::class); }
}
