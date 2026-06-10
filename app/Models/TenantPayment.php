<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPayment extends Model
{
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'subscription_id');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(PlatformUser::class, 'received_by');
    }

    // Helper methods

    /**
     * Get payment method display name.
     */
    public function getMethodDisplayAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card',
            'cheque' => 'Cheque',
            'online' => 'Online Payment',
            default => ucfirst($this->payment_method),
        };
    }

    /**
     * Available payment methods.
     */
    public static function paymentMethods(): array
    {
        return [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card',
            'cheque' => 'Cheque',
            'online' => 'Online Payment',
        ];
    }
}
