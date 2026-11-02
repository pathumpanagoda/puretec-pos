<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'store_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'device_id',
        'user_agent',
        'description',
        'severity',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function store(): BelongsTo { return $this->belongsTo(Store::class); }
    public function user(): BelongsTo  { return $this->belongsTo(User::class); }

    // Scopes
    public function scopeCritical($query)  { return $query->where('severity', 'critical'); }
    public function scopeForModel($query, string $type, int $id) {
        return $query->where('model_type', $type)->where('model_id', $id);
    }
    public function scopeRecent($query, int $hours = 24) {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
