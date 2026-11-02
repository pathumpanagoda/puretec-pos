<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientFeature extends Model
{
    protected $fillable = [
        'tenant_id',
        'feature_code',
        'is_enabled',
        'config',
        'enabled_at',
        'disabled_at',
    ];

    protected $casts = [
        'is_enabled'  => 'boolean',
        'config'      => 'array',
        'enabled_at'  => 'datetime',
        'disabled_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
