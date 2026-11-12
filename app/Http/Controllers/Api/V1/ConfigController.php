<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConfigController extends Controller
{
    protected FeatureService $featureService;

    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * Identify a client securely by code, id, or slug.
     *
     * GET/POST /api/v1/client/identify/{code?}
     *
     * Used by mobile apps (Flutter) to securely resolve the client store
     * before login, without ever exposing credentials, secrets, or internal server paths.
     */
    public function identifyClient(Request $request, ?string $code = null)
    {
        $clientCode = trim($code ?? $request->input('code') ?? $request->input('client_code') ?? '');

        if (empty($clientCode)) {
            return response()->json([
                'success' => false,
                'message' => 'Client code is required.',
            ], 422);
        }

        $tenant = Tenant::where('code', $clientCode)
            ->orWhere('id', is_numeric($clientCode) ? (int)$clientCode : 0)
            ->first();

        // Fallback search by slug of business name
        if (!$tenant) {
            $slug = Str::slug($clientCode);
            $tenant = Tenant::all()->first(function ($t) use ($slug) {
                return Str::slug($t->business_name) === $slug;
            });
        }

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found with the provided code.',
            ], 404);
        }

        if (!$tenant->is_active || $tenant->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'This client account is currently suspended or inactive.',
                'status'  => 'suspended',
            ], 403);
        }

        $defaultStore = $tenant->stores()->where('is_active', true)->first();
        $slug = Str::slug($tenant->business_name);

        return response()->json([
            'success' => true,
            'data'    => [
                'client_id'       => $tenant->id,
                'client_code'     => $tenant->code,
                'business_name'   => $tenant->business_name,
                'subdomain'       => $slug . '.puretec.lk',
                'currency'        => $defaultStore->currency ?? config('pos.currency', 'LKR'),
                'currency_symbol' => $defaultStore->currency_symbol ?? config('pos.currency_symbol', 'Rs.'),
                'is_active'       => (bool) $tenant->is_active,
                'plan'            => $tenant->subscription_plan,
                'api_endpoint'    => url('/api/v1'),
            ],
        ]);
    }

    /**
     * Get full mobile configuration.
     *
     * GET /api/v1/config
     *
     * Returns everything a mobile/PWA app needs to bootstrap:
     * - client info
     * - user info & permissions
     * - enabled features
     * - store configuration
     * - API endpoints
     *
     * NEVER exposes: DB credentials, internal keys, server paths.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $store = $user->store;
        $tenant = $user->tenant;
        $enabledFeatures = $this->featureService->getEnabledFeatures($user->tenant_id);

        return response()->json([
            'success' => true,
            'data'    => [
                'client' => $tenant ? [
                    'id'            => $tenant->id,
                    'code'          => $tenant->code,
                    'business_name' => $tenant->business_name,
                    'is_active'     => $tenant->is_active,
                    'plan'          => $tenant->subscription_plan,
                ] : null,

                'user' => [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'username'    => $user->username,
                    'role'        => $user->role,
                    'permissions' => $user->permissions ?? $user->getRolePermissions(),
                    'store_id'    => $user->store_id,
                    'tenant_id'   => $user->tenant_id,
                    'payment_mode'=> $user->payment_mode,
                ],

                'store' => $store ? [
                    'id'              => $store->id,
                    'name'            => $store->name,
                    'address'         => $store->address,
                    'phone'           => $store->phone,
                    'email'           => $store->email,
                    'currency'        => $store->currency ?? 'LKR',
                    'currency_symbol' => $store->currency_symbol ?? 'Rs.',
                    'tax_rate'        => $store->tax_rate ?? 0,
                    'timezone'        => config('app.timezone', 'Asia/Colombo'),
                ] : null,

                'features'    => $enabledFeatures,
                'permissions' => $user->permissions ?? $user->getRolePermissions(),

                'configuration' => [
                    'currency'            => config('pos.currency', 'LKR'),
                    'currency_symbol'     => config('pos.currency_symbol', 'Rs.'),
                    'payment_methods'     => array_keys(config('pos.payment_methods', ['cash' => 'Cash', 'card' => 'Card'])),
                    'receipt_footer'      => config('pos.receipt_footer', 'Thank you!'),
                    'low_stock_threshold' => config('pos.low_stock_threshold', 10),
                    'sync_interval'       => 300, // seconds
                    'heartbeat_interval'  => 15,  // seconds
                ],

                'api' => [
                    'base_url'     => config('app.url') . '/api/v1',
                    'sync_push'    => '/api/v1/sync/push',
                    'sync_pull'    => '/api/v1/sync/pull',
                    'sync_status'  => '/api/v1/sync/status',
                    'health'       => '/api/v1/health',
                    'version'      => config('pos.version', '2.0.0'),
                ],
            ],
        ]);
    }
}
