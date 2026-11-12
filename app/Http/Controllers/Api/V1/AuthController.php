<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DeviceRegistration;
use App\Services\AuditLogService;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected AuditLogService $auditLog;
    protected FeatureService $featureService;

    public function __construct(AuditLogService $auditLog, FeatureService $featureService)
    {
        $this->auditLog = $auditLog;
        $this->featureService = $featureService;
    }

    /**
     * Login and return Sanctum token.
     *
     * POST /api/v1/auth/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'        => 'required|string',
            'password'     => 'required|string',
            'client_code'  => 'nullable|string|max:64',
            'device_id'    => 'nullable|string|max:64',
            'device_name'  => 'nullable|string|max:255',
            'device_type'  => 'nullable|in:web,pwa,mobile_android,mobile_ios',
            'app_version'  => 'nullable|string|max:20',
            'os_info'      => 'nullable|string|max:255',
        ]);

        // Try email or username login
        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $query = User::where($field, $request->login)->where('is_active', true);

        // Scope to tenant if client_code provided
        if ($request->filled('client_code')) {
            $clientCode = trim($request->client_code);
            $tenant = \App\Models\Tenant::where('code', $clientCode)
                ->orWhere('id', is_numeric($clientCode) ? (int)$clientCode : 0)
                ->first();

            if (!$tenant) {
                $slug = Str::slug($clientCode);
                $tenant = \App\Models\Tenant::all()->first(fn($t) => Str::slug($t->business_name) === $slug);
            }

            if ($tenant) {
                $query->where('tenant_id', $tenant->id);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid client code.',
                ], 404);
            }
        }

        $user = $query->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->auditLog->logFailedLogin($request->login);
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Check tenant accessibility
        if ($user->tenant && !$user->tenant->isAccessible()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended. Please contact support.',
            ], 403);
        }

        // Create token with abilities based on user role
        $abilities = $this->getTokenAbilities($user);
        $token = $user->createToken(
            $request->device_name ?? 'API Token',
            $abilities,
            now()->addDays(30)
        );

        // Update login tracking
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Register/update device
        if ($request->device_id) {
            $this->registerOrUpdateDevice($user, $request);
        }

        // Log the login
        Auth::setUser($user);
        $this->auditLog->logLogin($request->device_type ?? 'api');

        // Get enabled features for this client
        $enabledFeatures = $this->featureService->getEnabledFeatures($user->tenant_id);

        return response()->json([
            'success' => true,
            'data'    => [
                'token'    => $token->plainTextToken,
                'user'     => [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'username'    => $user->username,
                    'role'        => $user->role,
                    'permissions' => $user->permissions ?? $user->getRolePermissions(),
                    'store_id'    => $user->store_id,
                    'tenant_id'   => $user->tenant_id,
                ],
                'store'    => $user->store ? [
                    'id'              => $user->store->id,
                    'name'            => $user->store->name,
                    'currency'        => $user->store->currency,
                    'currency_symbol' => $user->store->currency_symbol,
                    'tax_rate'        => $user->store->tax_rate,
                ] : null,
                'features' => $enabledFeatures,
                'config'   => [
                    'currency'        => config('pos.currency', 'LKR'),
                    'currency_symbol' => config('pos.currency_symbol', 'Rs.'),
                    'payment_methods' => array_keys(config('pos.payment_methods', [])),
                ],
            ],
        ]);
    }

    /**
     * Get authenticated user info.
     *
     * GET /api/v1/auth/me
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $enabledFeatures = $this->featureService->getEnabledFeatures($user->tenant_id);

        return response()->json([
            'success' => true,
            'data'    => [
                'user'     => [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'username'    => $user->username,
                    'role'        => $user->role,
                    'permissions' => $user->permissions ?? $user->getRolePermissions(),
                    'store_id'    => $user->store_id,
                    'tenant_id'   => $user->tenant_id,
                ],
                'features' => $enabledFeatures,
            ],
        ]);
    }

    /**
     * Logout (revoke current token).
     *
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request)
    {
        $this->auditLog->logLogout();
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Refresh token (revoke old, issue new).
     *
     * POST /api/v1/auth/refresh
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $oldToken = $user->currentAccessToken();
        $abilities = $oldToken->abilities;

        // Delete old token
        $oldToken->delete();

        // Create new token
        $newToken = $user->createToken(
            $oldToken->name,
            $abilities,
            now()->addDays(30)
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'token' => $newToken->plainTextToken,
            ],
        ]);
    }

    /**
     * Register a device.
     *
     * POST /api/v1/auth/register-device
     */
    public function registerDevice(Request $request)
    {
        $request->validate([
            'device_id'   => 'required|string|max:64',
            'device_name' => 'nullable|string|max:255',
            'device_type' => 'required|in:web,pwa,mobile_android,mobile_ios',
            'app_version' => 'nullable|string|max:20',
            'os_info'     => 'nullable|string|max:255',
        ]);

        $device = DeviceRegistration::updateOrCreate(
            ['device_id' => $request->device_id],
            [
                'device_name'  => $request->device_name,
                'device_type'  => $request->device_type,
                'app_version'  => $request->app_version,
                'os_info'      => $request->os_info,
                'last_seen_at' => now(),
                'is_active'    => true,
            ]
        );

        return response()->json([
            'success' => true,
            'data'    => ['device_id' => $device->device_id],
        ]);
    }

    /**
     * Get token abilities based on user role.
     */
    protected function getTokenAbilities(User $user): array
    {
        $base = ['sync:push', 'sync:pull', 'products:read', 'categories:read', 'customers:read'];

        if ($user->canAccess('pos')) {
            $base[] = 'sales:create';
            $base[] = 'payments:create';
        }
        if ($user->canAccess('customers')) {
            $base[] = 'customers:create';
            $base[] = 'customers:update';
        }
        if ($user->canAccess('inventory')) {
            $base[] = 'inventory:read';
            $base[] = 'inventory:adjust';
        }
        if ($user->isAdmin()) {
            $base[] = '*'; // Admin gets full API access
        }

        return $base;
    }

    /**
     * Register or update a device for the user.
     */
    protected function registerOrUpdateDevice(User $user, Request $request): void
    {
        DeviceRegistration::updateOrCreate(
            ['device_id' => $request->device_id],
            [
                'tenant_id'    => $user->tenant_id,
                'store_id'     => $user->store_id,
                'user_id'      => $user->id,
                'device_name'  => $request->device_name ?? 'Mobile Device',
                'device_type'  => $request->device_type ?? 'mobile_android',
                'app_version'  => $request->app_version ?? null,
                'os_info'      => $request->os_info ?? null,
                'last_seen_at' => now(),
                'is_active'    => true,
            ]
        );
    }
}
