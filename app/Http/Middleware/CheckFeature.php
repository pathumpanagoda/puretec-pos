<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\FeatureService;

class CheckFeature
{
    protected FeatureService $featureService;

    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * Check if the requested feature is enabled for the current tenant.
     *
     * Usage: Route::middleware('feature:inventory')
     *
     * This enforces LEVEL 1 (client feature access) from the permission architecture.
     * Combined with LEVEL 2 (user permission), a user can only access a feature if:
     *   - CLIENT FEATURE = ENABLED  AND
     *   - USER PERMISSION = ALLOWED
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        $tenantId = $user->tenant_id;

        if (!$this->featureService->isEnabled($tenantId, $feature)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error'   => 'Feature not available',
                    'message' => 'This feature is not enabled for your account.',
                    'feature' => $feature,
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'This feature is not available for your account. Please contact support.');
        }

        return $next($request);
    }
}
