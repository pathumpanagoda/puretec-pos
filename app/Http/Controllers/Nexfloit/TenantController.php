<?php

namespace App\Http\Controllers\Nexfloit;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Models\Store;
use App\Services\TenantSetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    protected TenantSetupService $setupService;

    public function __construct(TenantSetupService $setupService)
    {
        $this->setupService = $setupService;
    }
    /**
     * List all tenants with search and filter.
     */
    public function index(Request $request)
    {
        $query = Tenant::query();

        // Search by name, email, or code
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            match ($status) {
                'active' => $query->where('is_active', true)->where('is_locked', false),
                'locked' => $query->where('is_locked', true),
                'inactive' => $query->where('is_active', false),
                'trial' => $query->where('trial_ends_at', '>', now()),
                default => null,
            };
        }

        // Filter by plan
        if ($plan = $request->input('plan')) {
            $query->where('subscription_plan', $plan);
        }

        $tenants = $query->latest()->paginate(15);

        return view('nexfloit.tenants.index', compact('tenants'));
    }

    /**
     * Show create tenant form.
     */
    public function create()
    {
        return view('nexfloit.tenants.create');
    }

    /**
     * Store new tenant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'subscription_plan' => 'required|in:basic,standard,premium',
            'monthly_fee' => 'required|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0|max:90',
            // Initial admin user
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:6',
            // Initial store
            'store_name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Create tenant
            $tenant = Tenant::create([
                'code' => Tenant::generateCode(),
                'business_name' => $validated['business_name'],
                'owner_name' => $validated['owner_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'subscription_plan' => $validated['subscription_plan'],
                'monthly_fee' => $validated['monthly_fee'],
                'is_active' => true,
                'is_locked' => false,
                'trial_ends_at' => isset($validated['trial_days']) && $validated['trial_days'] > 0
                    ? now()->addDays($validated['trial_days'])
                    : null,
            ]);

            // Create initial store
            $store = Store::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['store_name'],
                'code' => 'STR001',
                'currency' => 'LKR',
                'currency_symbol' => 'Rs.',
                'is_active' => true,
            ]);

            // Set up default categories, subcategories, expense categories,
            // customer groups, taxes, and walk-in customer
            $this->setupService->setupStoreDefaults($store);

            // Create admin user for tenant
            User::create([
                'tenant_id' => $tenant->id,
                'store_id' => $store->id,
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'username' => strtolower(str_replace(' ', '', $validated['admin_name'])),
                'password' => Hash::make($validated['admin_password']),
                'role' => 'super_admin',
                'is_active' => true,
            ]);

            // Create first subscription if not on trial
            if (!$tenant->isOnTrial()) {
                TenantSubscription::createForMonth($tenant, now()->format('Y-m'));
            }

            DB::commit();

            return redirect()->route('nexfloit.tenants.show', $tenant)
                ->with('success', 'Tenant created successfully! Code: ' . $tenant->code);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create tenant: ' . $e->getMessage());
        }
    }

    /**
     * Show tenant details.
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['stores', 'subscriptions' => function ($q) {
            $q->latest('billing_month')->take(12);
        }, 'payments' => function ($q) {
            $q->latest()->take(10);
        }]);

        // Get tenant statistics
        $stats = [
            'stores_count' => $tenant->stores()->count(),
            'users_count' => $tenant->users()->count(),
            'total_paid' => $tenant->payments()->sum('amount'),
            'pending_amount' => $tenant->pendingAmount(),
        ];

        return view('nexfloit.tenants.show', compact('tenant', 'stats'));
    }

    /**
     * Show edit tenant form.
     */
    public function edit(Tenant $tenant)
    {
        return view('nexfloit.tenants.edit', compact('tenant'));
    }

    /**
     * Update tenant.
     */
    public function update(Request $request, Tenant $tenant)
    {
        // Get admin user ID from form
        $adminUserId = $request->input('admin_user_id');

        $rules = [
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email,' . $tenant->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'subscription_plan' => 'required|in:basic,standard,premium',
            'monthly_fee' => 'required|numeric|min:0',
            // Admin user credentials
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email,' . $adminUserId,
            'admin_username' => 'nullable|string|unique:users,username,' . $adminUserId,
        ];

        // Add password validation if password is being changed
        if ($request->filled('admin_password')) {
            $rules['admin_password'] = 'required|string|min:6|confirmed';
        }

        $validated = $request->validate($rules);

        // Handle is_active checkbox (unchecked = not sent)
        $validated['is_active'] = $request->boolean('is_active');

        // Update tenant with payment settings
        $tenant->update([
            'business_name' => $validated['business_name'],
            'owner_name' => $validated['owner_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'subscription_plan' => $validated['subscription_plan'],
            'monthly_fee' => $validated['monthly_fee'],
            'is_active' => $validated['is_active'],
            // Payment reminder settings
            'billing_contact_email' => $request->input('billing_contact_email'),
            'billing_contact_phone' => $request->input('billing_contact_phone'),
            'payment_due_day' => $request->input('payment_due_day', 1),
            'payment_reminder_enabled' => $request->boolean('payment_reminder_enabled'),
            'next_payment_due' => $request->input('next_payment_due') ?: null,
            'payment_notes' => $request->input('payment_notes'),
        ]);

        // Update admin user credentials
        // Security: Verify user belongs to this tenant
        $adminUser = User::where('id', $adminUserId)
            ->where('tenant_id', $tenant->id)
            ->first();
        $credentialsChanged = false;

        if ($adminUser) {
            $adminData = [
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'username' => $validated['admin_username'],
            ];

            // Check if email or username changed
            if ($adminUser->email !== $validated['admin_email'] ||
                $adminUser->username !== $validated['admin_username']) {
                $credentialsChanged = true;
            }

            // Update password if provided
            if ($request->filled('admin_password')) {
                $adminData['password'] = Hash::make($request->admin_password);
                $adminData['password_changed_at'] = now();
                $adminData['password_changed_by'] = 'nexfloit_admin';
                $credentialsChanged = true;
            }

            $adminUser->update($adminData);
        }

        $message = $credentialsChanged
            ? 'Tenant and login credentials updated successfully!'
            : 'Tenant updated successfully!';

        return redirect()->route('nexfloit.tenants.show', $tenant)
            ->with('success', $message);
    }

    /**
     * Delete tenant and ALL related data completely.
     */
    public function destroy(Tenant $tenant)
    {
        DB::beginTransaction();

        try {
            // Get all store IDs for this tenant
            $storeIds = $tenant->stores()->pluck('id')->toArray();

            if (!empty($storeIds)) {
                // Helper function to safely delete from table if it exists
                $safeDelete = function ($table, $callback) use ($storeIds) {
                    if (\Schema::hasTable($table)) {
                        $callback($table, $storeIds);
                    }
                };

                // 1. Delete order items first
                $safeDelete('order_items', function ($table, $storeIds) {
                    DB::table($table)->whereIn('order_id', function ($query) use ($storeIds) {
                        $query->select('id')->from('orders')->whereIn('store_id', $storeIds);
                    })->delete();
                });

                // 2. Delete orders
                $safeDelete('orders', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 3. Delete inventory movements
                $safeDelete('inventory_movements', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 4. Delete products
                $safeDelete('products', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 5. Delete categories (children first, then parents)
                $safeDelete('categories', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->whereNotNull('parent_id')->delete();
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 6. Delete customers
                $safeDelete('customers', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 7. Delete customer groups
                $safeDelete('customer_groups', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 8. Delete suppliers
                $safeDelete('suppliers', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 9. Delete expenses
                $safeDelete('expenses', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 10. Delete expense categories
                $safeDelete('expense_categories', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 11. Delete taxes
                $safeDelete('taxes', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 12. Delete discounts
                $safeDelete('discounts', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 13. Delete registers
                $safeDelete('registers', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 14. Delete settings
                $safeDelete('settings', function ($table, $storeIds) {
                    DB::table($table)->whereIn('store_id', $storeIds)->delete();
                });

                // 15. Delete users belonging to this tenant
                DB::table('users')->where('tenant_id', $tenant->id)->delete();

                // 16. Delete stores
                DB::table('stores')->whereIn('id', $storeIds)->delete();
            }

            // Delete tenant subscriptions
            DB::table('tenant_subscriptions')->where('tenant_id', $tenant->id)->delete();

            // Delete tenant payments
            DB::table('tenant_payments')->where('tenant_id', $tenant->id)->delete();

            // Finally delete the tenant
            $tenant->delete();

            DB::commit();

            return redirect()->route('nexfloit.tenants.index')
                ->with('success', 'Tenant and all related data deleted completely!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete tenant: ' . $e->getMessage());
        }
    }

    /**
     * Lock tenant for non-payment.
     */
    public function lock(Request $request, Tenant $tenant)
    {
        $reason = $request->input('reason', 'Payment overdue');
        $tenant->lock($reason);

        // Mark current subscription as locked
        $subscription = $tenant->currentSubscription();
        if ($subscription) {
            $subscription->markAsLocked();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tenant has been locked.',
            ]);
        }

        return back()->with('success', 'Tenant has been locked.');
    }

    /**
     * Unlock tenant.
     */
    public function unlock(Request $request, Tenant $tenant)
    {
        $tenant->unlock();

        // Update next payment due date to next month
        $tenant->update([
            'next_payment_due' => now()->startOfMonth()->addMonth()->addDays($tenant->payment_due_day - 1),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tenant has been unlocked.',
            ]);
        }

        return back()->with('success', 'Tenant has been unlocked.');
    }

    /**
     * Create a subscription for the tenant.
     */
    public function createSubscription(Request $request, Tenant $tenant)
    {
        $month = $request->input('month', now()->format('Y-m'));

        // Check if subscription already exists
        $existing = $tenant->subscriptions()->where('billing_month', $month)->first();
        if ($existing) {
            return back()->with('error', "Subscription for {$month} already exists.");
        }

        // Create subscription
        $subscription = TenantSubscription::createForMonth($tenant, $month);

        // Set next payment due if not set
        if (!$tenant->next_payment_due) {
            $tenant->update([
                'next_payment_due' => $subscription->due_date,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Subscription for {$month} created.",
                'subscription' => $subscription,
            ]);
        }

        return back()->with('success', "Subscription for {$month} created successfully!");
    }
}
