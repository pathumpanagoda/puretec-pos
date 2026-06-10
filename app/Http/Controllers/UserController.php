<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Only admins can manage users.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'You do not have permission to manage users.');
            }
            return $next($request);
        });
    }

    /**
     * Display list of users.
     */
    public function index()
    {
        $users = User::where('store_id', auth()->user()->store_id)
            ->orderBy('name')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    /**
     * Show form to create a new user.
     */
    public function create()
    {
        $roles = $this->getAvailableRoles();
        $permissions = User::getAllPermissions();

        return view('users.form', [
            'user'        => null,
            'roles'       => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'username'          => 'required|string|max:50|unique:users,username',
            'phone'             => 'nullable|string|max:20',
            'role'              => ['required', Rule::in(['admin', 'manager', 'cashier', 'inventory', 'viewer'])],
            'payment_mode'      => ['nullable', Rule::in(['full', 'bill_only'])],
            'password'          => 'required|string|min:6|confirmed',
            'is_active'         => 'boolean',
            'permissions'       => 'nullable|array',
            'security_question' => 'nullable|string|max:255',
            'security_answer'   => 'nullable|string|max:255',
        ]);

        // Create user
        $user = new User();
        $user->store_id     = auth()->user()->store_id;
        $user->name         = $validated['name'];
        $user->email        = $validated['email'];
        $user->username     = $validated['username'];
        $user->phone        = $validated['phone'] ?? null;
        $user->role         = $validated['role'];
        $user->payment_mode = $validated['payment_mode'] ?? 'full';
        $user->password     = Hash::make($validated['password']);
        $user->is_active    = $request->boolean('is_active', true);

        // Save security question if provided
        if (!empty($validated['security_question']) && !empty($validated['security_answer'])) {
            $user->security_question = $validated['security_question'];
            $user->security_answer = $validated['security_answer'];
        }

        // Save custom permissions if provided
        if (!empty($validated['permissions'])) {
            $user->permissions = $validated['permissions'];
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show form to edit user.
     */
    public function edit(User $user)
    {
        // Make sure user belongs to same store
        if ($user->store_id !== auth()->user()->store_id) {
            abort(403);
        }

        // Cannot edit super_admin
        if ($user->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'Cannot edit super admin.');
        }

        $roles = $this->getAvailableRoles();
        $permissions = User::getAllPermissions();

        return view('users.form', [
            'user'        => $user,
            'roles'       => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        // Make sure user belongs to same store
        if ($user->store_id !== auth()->user()->store_id) {
            abort(403);
        }

        // Cannot edit super_admin
        if ($user->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'Cannot edit super admin.');
        }

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'username'          => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone'             => 'nullable|string|max:20',
            'role'              => ['required', Rule::in(['admin', 'manager', 'cashier', 'inventory', 'viewer'])],
            'payment_mode'      => ['nullable', Rule::in(['full', 'bill_only'])],
            'password'          => 'nullable|string|min:6|confirmed',
            'is_active'         => 'boolean',
            'permissions'       => 'nullable|array',
            'security_question' => 'nullable|string|max:255',
            'security_answer'   => 'nullable|string|max:255',
        ]);

        // Update user fields
        $user->name         = $validated['name'];
        $user->email        = $validated['email'];
        $user->username     = $validated['username'];
        $user->phone        = $validated['phone'] ?? null;
        $user->role         = $validated['role'];
        $user->payment_mode = $validated['payment_mode'] ?? 'full';
        $user->is_active    = $request->boolean('is_active', true);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Update security question if provided
        if (!empty($validated['security_question'])) {
            $user->security_question = $validated['security_question'];
            if (!empty($validated['security_answer'])) {
                $user->security_answer = $validated['security_answer'];
            }
        } elseif ($request->has('security_question') && empty($validated['security_question'])) {
            // Clear security question if explicitly set to empty
            $user->security_question = null;
            $user->security_answer = null;
        }

        // Save custom permissions (or clear if empty)
        $user->permissions = !empty($validated['permissions']) ? $validated['permissions'] : null;

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete user.
     */
    public function destroy(User $user)
    {
        // Make sure user belongs to same store
        if ($user->store_id !== auth()->user()->store_id) {
            abort(403);
        }

        // Cannot delete super_admin
        if ($user->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'Cannot delete super admin.');
        }

        // Cannot delete yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Get roles that can be assigned (not super_admin).
     */
    private function getAvailableRoles(): array
    {
        return [
            'admin'     => 'Admin',
            'manager'   => 'Manager',
            'cashier'   => 'Cashier',
            'inventory' => 'Inventory Staff',
            'viewer'    => 'Viewer (Read Only)',
        ];
    }
}
