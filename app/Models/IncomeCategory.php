<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeCategory extends Model
{
    protected $fillable = ['store_id', 'name', 'code', 'description', 'color', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }
}
