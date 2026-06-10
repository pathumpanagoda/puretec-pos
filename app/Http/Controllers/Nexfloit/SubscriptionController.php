<?php

namespace App\Http\Controllers\Nexfloit;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\TenantPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /**
     * List all subscriptions with filters.
     */
    public function index(Request $request)
    {
        $query = TenantSubscription::with('tenant');

        // Filter by month
        $month = $request->input('month', now()->format('Y-m'));
        $query->where('billing_month', $month);

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Search by tenant
        if ($search = $request->input('search')) {
            $query->whereHas('tenant', function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->latest('due_date')->paginate(15);

        // Get available months for filter
        $availableMonths = TenantSubscription::distinct()
            ->orderBy('billing_month', 'desc')
            ->pluck('billing_month');

        // Summary stats
        $stats = [
            'total' => TenantSubscription::where('billing_month', $month)->sum('amount'),
            'paid' => TenantSubscription::where('billing_month', $month)->where('status', 'paid')->sum('amount'),
            'pending' => TenantSubscription::where('billing_month', $month)->whereIn('status', ['pending', 'overdue'])->sum('amount'),
            'locked' => TenantSubscription::where('billing_month', $month)->where('status', 'locked')->sum('amount'),
        ];

        return view('nexfloit.subscriptions.index', compact(
            'subscriptions',
            'availableMonths',
            'month',
            'stats'
        ));
    }

    /**
     * Show record payment form.
     */
    public function showRecordPayment(TenantSubscription $subscription)
    {
        $subscription->load('tenant');
        $paymentMethods = TenantPayment::paymentMethods();

        return view('nexfloit.subscriptions.record-payment', compact('subscription', 'paymentMethods'));
    }

    /**
     * Record payment for subscription.
     */
    public function recordPayment(Request $request, TenantSubscription $subscription)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Create payment record
            TenantPayment::create([
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'received_by' => $request->platformUser->id,
                'notes' => $validated['notes'],
            ]);

            // Mark subscription as paid
            $subscription->markAsPaid(
                $validated['payment_method'],
                $validated['reference_number']
            );

            // Unlock tenant if was locked
            $tenant = $subscription->tenant;
            if ($tenant->is_locked) {
                $tenant->unlock();
            }

            DB::commit();

            return redirect()->route('nexfloit.subscriptions.index')
                ->with('success', 'Payment recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Generate subscriptions for next month.
     */
    public function generateMonthly(Request $request)
    {
        $month = $request->input('month', now()->addMonth()->format('Y-m'));

        $tenants = Tenant::where('is_active', true)
            ->whereDoesntHave('subscriptions', function ($q) use ($month) {
                $q->where('billing_month', $month);
            })
            ->get();

        $count = 0;
        foreach ($tenants as $tenant) {
            // Skip tenants on trial for that month
            if ($tenant->trial_ends_at && $tenant->trial_ends_at->format('Y-m') >= $month) {
                continue;
            }

            TenantSubscription::createForMonth($tenant, $month);
            $count++;
        }

        return back()->with('success', "Generated {$count} subscriptions for {$month}.");
    }
}
