<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'store_id',
        'user_id',
        'device_id',
        'direction',
        'entity_type',
        'entity_uuid',
        'entity_id',
        'status',
        'error_message',
        'payload_summary',
        'batch_size',
        'duration_ms',
    ];

    protected $casts = [
        'payload_summary' => 'array',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function store(): BelongsTo  { return $this->belongsTo(Store::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }

    // Scopes
    public function scopeFailed($query)    { return $query->where('status', 'failed'); }
    public function scopeConflicts($query) { return $query->where('status', 'conflict'); }
    public function scopeForDevice($query, string $deviceId) { return $query->where('device_id', $deviceId); }
}
