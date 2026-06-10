<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantSubscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'billing_month',
        'amount',
        'due_date',
        'status',
        'paid_at',
        'payment_method',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TenantPayment::class, 'subscription_id');
    }

    // Helper methods

    /**
     * Check if subscription is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    /**
     * Check if payment is due soon (within 5 days).
     */
    public function isDueSoon(): bool
    {
        if ($this->status === 'paid') {
            return false;
        }

        $daysUntilDue = now()->diffInDays($this->due_date, false);
        return $daysUntilDue >= 0 && $daysUntilDue <= 5;
    }

    /**
     * Mark subscription as paid.
     */
    public function markAsPaid(string $method, ?string $reference = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $method,
            'payment_reference' => $reference,
        ]);
    }

    /**
     * Mark subscription as overdue.
     */
    public function markAsOverdue(): void
    {
        if ($this->status === 'pending') {
            $this->update(['status' => 'overdue']);
        }
    }

    /**
     * Mark subscription as locked.
     */
    public function markAsLocked(): void
    {
        $this->update(['status' => 'locked']);
    }

    /**
     * Get status badge info.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'paid' => ['label' => 'Paid', 'class' => 'badge-success'],
            'pending' => ['label' => 'Pending', 'class' => 'badge-warning'],
            'overdue' => ['label' => 'Overdue', 'class' => 'badge-danger'],
            'locked' => ['label' => 'Locked', 'class' => 'badge-dark'],
            default => ['label' => ucfirst($this->status), 'class' => 'badge-secondary'],
        };
    }

    /**
     * Get formatted billing month.
     */
    public function getBillingMonthDisplayAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->billing_month)->format('F Y');
    }

    /**
     * Create subscription for next month.
     */
    public static function createForMonth(Tenant $tenant, string $month): self
    {
        $dueDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        return self::create([
            'tenant_id' => $tenant->id,
            'billing_month' => $month,
            'amount' => $tenant->monthly_fee,
            'due_date' => $dueDate,
            'status' => 'pending',
        ]);
    }
}
