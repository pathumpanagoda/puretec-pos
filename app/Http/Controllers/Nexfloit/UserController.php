<?php

namespace App\Http\Controllers\Nexfloit;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * List all users across all tenants.
     */
    public function index(Request $request)
    {
        $query = User::with('tenant', 'store');

        // Filter by tenant
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $tenants = Tenant::orderBy('business_name')->get();

        return view('nexfloit.users.index', compact('users', 'tenants'));
    }

    /**
     * Show user details.
     */
    public function show(User $user)
    {
        $user->load('tenant', 'store');

        return view('nexfloit.users.show', compact('user'));
    }

    /**
     * Show edit form.
     */
    public function edit(User $user)
    {
        $user->load('tenant', 'store');
        $tenants = Tenant::with('stores')->orderBy('business_name')->get();

        return view('nexfloit.users.edit', compact('user', 'tenants'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'username' => 'nullable|string|unique:users,username,' . $user->id,
            'role' => 'required|in:admin,manager,cashier',
            'is_active' => 'boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('nexfloit.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Reset user password (without email).
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'password_changed_by' => 'nexfloit_admin',
        ]);

        return back()->with('success', 'Password reset successfully for ' . $user->name);
    }

    /**
     * Generate random password for user.
     */
    public function generatePassword(User $user)
    {
        $newPassword = Str::random(10);

        $user->update([
            'password' => Hash::make($newPassword),
            'password_changed_at' => now(),
            'password_changed_by' => 'nexfloit_admin',
        ]);

        return back()->with('success', "New password for {$user->name}: {$newPassword}");
    }

    /**
     * Impersonate user (login as them).
     */
    public function impersonate(User $user)
    {
        // Store original admin session
        session(['nexfloit_impersonating' => true]);
        session(['nexfloit_original_url' => url()->previous()]);

        // Login as the user
        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('success', 'You are now logged in as ' . $user->name . '. Click "Exit Impersonation" to return.');
    }

    /**
     * Stop impersonating and return to Nexfloit admin.
     */
    public function stopImpersonate()
    {
        // Logout from user
        auth()->logout();

        // Clear impersonation session
        $originalUrl = session('nexfloit_original_url', route('nexfloit.users.index'));
        session()->forget(['nexfloit_impersonating', 'nexfloit_original_url']);

        return redirect()->route('nexfloit.login')
            ->with('success', 'Returned to Nexfloit admin. Please login again.');
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$user->name} has been {$status}.");
    }
}
