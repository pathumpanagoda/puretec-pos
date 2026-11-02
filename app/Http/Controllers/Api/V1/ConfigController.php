<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\FeatureService;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    protected FeatureService $featureService;

    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
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
