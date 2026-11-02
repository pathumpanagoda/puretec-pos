<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceRegistration extends Model
{
    protected $fillable = [
        'tenant_id',
        'store_id',
        'user_id',
        'device_id',
        'device_name',
        'device_type',
        'app_version',
        'os_info',
        'is_active',
        'last_seen_at',
        'last_sync_at',
        'pending_sync_count',
        'capabilities',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'last_seen_at'       => 'datetime',
        'last_sync_at'       => 'datetime',
        'capabilities'       => 'array',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function store(): BelongsTo  { return $this->belongsTo(Store::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
}
