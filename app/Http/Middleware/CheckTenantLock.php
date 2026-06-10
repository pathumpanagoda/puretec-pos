<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantLock
{
    /**
     * Check if tenant system is locked due to non-payment.
     * If locked, show the lock screen instead of normal content.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->tenant_id) {
            return $next($request);
        }

        $tenant = $user->tenant;

        // Check if tenant is locked
        if ($tenant && $tenant->is_locked) {
            // Allow logout route
            if ($request->is('logout') || $request->is('*/logout')) {
                return $next($request);
            }

            // Return locked screen view
            return response()->view('components.system-locked-screen', [
                'tenant' => $tenant,
                'pendingAmount' => $tenant->pendingAmount(),
            ], 423); // 423 Locked HTTP status
        }

        // Check if tenant is inactive
        if ($tenant && !$tenant->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated. Please contact support.');
        }

        // Share payment reminder data if payment is due soon
        if ($tenant && ($tenant->payment_reminder_enabled ?? true)) {
            $showReminder = false;
            $amount = $tenant->monthly_fee;
            $dueDate = null;
            $billingMonth = now()->format('F Y');

            // Option 1: Check if tenant has a manually set next_payment_due
            if ($tenant->next_payment_due) {
                $daysUntilDue = now()->diffInDays($tenant->next_payment_due, false);
                // Show reminder if due within 7 days (past or future)
                if ($daysUntilDue >= -7 && $daysUntilDue <= 7) {
                    $showReminder = true;
                    $dueDate = $tenant->next_payment_due;
                    $billingMonth = $tenant->next_payment_due->format('F Y');
                }
            }

            // Option 2: Check for pending/overdue subscriptions
            if (!$showReminder) {
                $pendingSubscription = $tenant->subscriptions()
                    ->whereIn('status', ['pending', 'overdue'])
                    ->orderBy('due_date', 'asc')
                    ->first();

                if ($pendingSubscription) {
                    $daysUntilDue = now()->diffInDays($pendingSubscription->due_date, false);
                    // Show if within 7 days of due date or already overdue
                    if ($daysUntilDue <= 7) {
                        $showReminder = true;
                        $amount = $pendingSubscription->amount;
                        $dueDate = $pendingSubscription->due_date;
                        $billingMonth = $pendingSubscription->billing_month_display;
                    }
                }
            }

            // Option 3: Check current month subscription (original logic)
            if (!$showReminder) {
                $subscription = $tenant->currentSubscription();
                if ($subscription && $subscription->isDueSoon()) {
                    $showReminder = true;
                    $amount = $subscription->amount;
                    $dueDate = $subscription->due_date;
                    $billingMonth = $subscription->billing_month_display;
                }
            }

            if ($showReminder && $dueDate) {
                view()->share('paymentReminder', [
                    'show' => true,
                    'amount' => $amount,
                    'dueDate' => $dueDate,
                    'billingMonth' => $billingMonth,
                    // Use tenant-specific contact info or defaults
                    'email' => $tenant->billing_contact_email ?? 'billing@nexfloit.com',
                    'phone' => $tenant->billing_contact_phone ?? '+94 77 123 4567',
                ]);
            }
        }

        return $next($request);
    }
}
