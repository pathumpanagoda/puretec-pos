<?php

namespace App\Http\Controllers\Puretec;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\TenantPayment;
use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        // Pending password reset requests count (all stores)
        $pendingPasswordResets = PasswordResetRequest::pending()->count();

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

        return view('puretec.dashboard', compact(
            'totalTenants',
            'activeTenants',
            'lockedTenants',
            'trialTenants',
            'expectedRevenue',
            'collectedRevenue',
            'pendingPayments',
            'pendingPasswordResets',
            'recentTenants',
            'recentPayments',
            'overdueSubscriptions',
            'currentMonth'
        ));
    }

    /**
     * Show all password reset requests (all stores).
     */
    public function passwordResetRequests(Request $request)
    {
        $query = PasswordResetRequest::with(['user', 'store'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(20);
        $pendingCount = PasswordResetRequest::pending()->count();

        return view('puretec.password-reset-requests', compact('requests', 'pendingCount'));
    }

    /**
     * Approve a password reset request (from Puretec admin).
     */
    public function approvePasswordReset($id)
    {
        $resetRequest = PasswordResetRequest::findOrFail($id);

        if (!$resetRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $token = Str::random(64);

        $resetRequest->update([
            'status'           => 'approved',
            'token'            => Hash::make($token),
            'approved_by_type' => 'platform_admin',
            'approved_by_id'   => session('puretec_user_id'),
            'approved_at'      => now(),
            'expires_at'       => now()->addHours(1),
        ]);

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $resetRequest->user->email,
        ]);

        return back()->with('success', 'Password reset request approved for ' . $resetRequest->user->name . '!')
                      ->with('reset_url', $resetUrl);
    }

    /**
     * Reject a password reset request (from Puretec admin).
     */
    public function rejectPasswordReset($id)
    {
        $resetRequest = PasswordResetRequest::findOrFail($id);

        if (!$resetRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $resetRequest->update([
            'status'           => 'rejected',
            'approved_by_type' => 'platform_admin',
            'approved_by_id'   => session('puretec_user_id'),
            'rejected_at'      => now(),
        ]);

        return back()->with('success', 'Password reset request has been rejected.');
    }
}

