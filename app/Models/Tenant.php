<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'code',
        'business_name',
        'owner_name',
        'email',
        'phone',
        'address',
        'logo',
        'subscription_plan',
        'monthly_fee',
        'billing_contact_email',
        'billing_contact_phone',
        'payment_due_day',
        'payment_reminder_enabled',
        'next_payment_due',
        'payment_notes',
        'is_active',
        'is_locked',
        'locked_at',
        'lock_reason',
        'trial_ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'monthly_fee' => 'decimal:2',
        'payment_reminder_enabled' => 'boolean',
        'next_payment_due' => 'date',
        'payment_due_day' => 'integer',
    ];

    // Relationships
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TenantPayment::class);
    }

    // Helper methods

    /**
     * Check if tenant system is accessible (active and not locked).
     */
    public function isAccessible(): bool
    {
        return $this->is_active && !$this->is_locked;
    }

    /**
     * Check if tenant is in trial period.
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Lock the tenant system for non-payment.
     */
    public function lock(string $reason = 'Payment overdue'): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'lock_reason' => $reason,
        ]);
    }

    /**
     * Unlock the tenant system.
     */
    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'lock_reason' => null,
        ]);
    }

    /**
     * Get current month's subscription.
     */
    public function currentSubscription(): ?TenantSubscription
    {
        return $this->subscriptions()
            ->where('billing_month', now()->format('Y-m'))
            ->first();
    }

    /**
     * Get pending/overdue subscription amount.
     */
    public function pendingAmount(): float
    {
        return $this->subscriptions()
            ->whereIn('status', ['pending', 'overdue'])
            ->sum('amount');
    }

    /**
     * Generate unique tenant code.
     */
    public static function generateCode(): string
    {
        $prefix = 'TEN';
        $lastTenant = self::orderBy('id', 'desc')->first();
        $number = $lastTenant ? ((int) substr($lastTenant->code, 3)) + 1 : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get plan display name.
     */
    public function getPlanDisplayAttribute(): string
    {
        return ucfirst($this->subscription_plan);
    }

    /**
     * Get plan badge info with colors.
     */
    public function getPlanBadgeAttribute(): array
    {
        return match ($this->subscription_plan) {
            'basic' => ['label' => 'Basic', 'class' => 'badge-plan-basic'],
            'standard' => ['label' => 'Standard', 'class' => 'badge-plan-standard'],
            'premium' => ['label' => 'Premium', 'class' => 'badge-plan-premium'],
            default => ['label' => ucfirst($this->subscription_plan), 'class' => 'badge-secondary'],
        };
    }

    /**
     * Get status badge info.
     */
    public function getStatusBadgeAttribute(): array
    {
        if ($this->is_locked) {
            return ['label' => 'Locked', 'class' => 'bg-danger'];
        }
        if (!$this->is_active) {
            return ['label' => 'Inactive', 'class' => 'bg-secondary'];
        }
        if ($this->isOnTrial()) {
            return ['label' => 'Trial', 'class' => 'badge-plan-trial'];
        }
        return ['label' => 'Active', 'class' => 'bg-success'];
    }
}
