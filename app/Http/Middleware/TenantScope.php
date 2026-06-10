<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use App\Models\TenantSubscription;

class TenantScope
{
    /**
     * Automatically scope queries to the current tenant.
     * This ensures data isolation between tenants.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Get tenant from user
        $tenant = $user->tenant;

        if (!$tenant) {
            // User has no tenant - might be legacy data
            return $next($request);
        }

        // Store tenant in session and request
        session(['current_tenant_id' => $tenant->id]);
        $request->merge(['tenant' => $tenant]);

        // Share with views
        view()->share('currentTenant', $tenant);

        // Check for payment reminder (only for admin users)
        if ($user->isAdmin()) {
            $paymentReminder = $this->checkPaymentReminder($tenant);
            view()->share('paymentReminder', $paymentReminder);
        }

        return $next($request);
    }

    /**
     * Check if tenant has payment due soon and prepare reminder data.
     */
    protected function checkPaymentReminder(Tenant $tenant): array
    {
        // Find pending/overdue subscription
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->first();

        if (!$subscription) {
            return ['show' => false];
        }

        // Show reminder if due within 7 days or overdue
        $daysUntilDue = now()->diffInDays($subscription->due_date, false);
        $showReminder = $daysUntilDue <= 7; // Show 7 days before due date or if overdue

        if (!$showReminder) {
            return ['show' => false];
        }

        return [
            'show' => true,
            'billingMonth' => $subscription->billing_month_display,
            'amount' => $subscription->amount,
            'dueDate' => $subscription->due_date,
            'isOverdue' => $subscription->isOverdue(),
            'daysUntilDue' => $daysUntilDue,
        ];
    }

    /**
     * Get current tenant from session.
     */
    public static function getCurrentTenant(): ?Tenant
    {
        $tenantId = session('current_tenant_id');
        return $tenantId ? Tenant::find($tenantId) : null;
    }

    /**
     * Get current tenant ID.
     */
    public static function getCurrentTenantId(): ?int
    {
        return session('current_tenant_id');
    }
}
