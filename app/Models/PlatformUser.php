<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformUser extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'security_question',
        'security_answer',
        'password_reset_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'security_answer',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'password_reset_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function paymentsReceived(): HasMany
    {
        return $this->hasMany(TenantPayment::class, 'received_by');
    }

    // Authorization helpers

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Check if user can manage tenants.
     */
    public function canManageTenants(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can record payments.
     */
    public function canRecordPayments(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can lock/unlock tenants.
     */
    public function canLockTenants(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Get role display name.
     */
    public function getRoleDisplayAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'support' => 'Support',
            default => ucfirst($this->role),
        };
    }

    /**
     * Available roles.
     */
    public static function roles(): array
    {
        return [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'support' => 'Support',
        ];
    }
}
