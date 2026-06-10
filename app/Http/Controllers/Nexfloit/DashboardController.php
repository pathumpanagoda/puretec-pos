<?php

namespace App\Http\Controllers\Nexfloit;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\TenantPayment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard with key metrics.
     */
    public function index(Request $request)
    {
        // Tenant statistics
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->where('is_locked', false)->count();
        $lockedTenants = Tenant::where('is_locked', true)->count();
        $trialTenants = Tenant::where('trial_ends_at', '>', now())->count();

        // Current month subscriptions
        $currentMonth = now()->format('Y-m');
        $monthlySubscriptions = TenantSubscription::where('billing_month', $currentMonth)->get();
        $expectedRevenue = $monthlySubscriptions->sum('amount');
        $collectedRevenue = $monthlySubscriptions->where('status', 'paid')->sum('amount');
        $pendingPayments = $monthlySubscriptions->whereIn('status', ['pending', 'overdue'])->count();

        // Recent activity
        $recentTenants = Tenant::latest()->take(5)->get();
        $recentPayments = TenantPayment::with('tenant')
            ->latest()
            ->take(5)
            ->get();

        // Overdue subscriptions
        $overdueSubscriptions = TenantSubscription::with('tenant')
            ->where('status', 'overdue')
            ->orWhere(function ($query) {
                $query->where('status', 'pending')
                    ->where('due_date', '<', now());
            })
            ->take(10)
            ->get();

        return view('nexfloit.dashboard', compact(
            'totalTenants',
            'activeTenants',
            'lockedTenants',
            'trialTenants',
            'expectedRevenue',
            'collectedRevenue',
            'pendingPayments',
            'recentTenants',
            'recentPayments',
            'overdueSubscriptions',
            'currentMonth'
        ));
    }
}
