<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Store;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    /**
     * Intercept requests to ensure onboarding is completed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for setup, license, api, or ajax requests to avoid redirect loops
        if ($request->is('setup*') || $request->is('license*') || $request->is('api/*') || $request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        // Check if onboarding is completed
        $store = Store::first();
        $onboardingCompleted = false;

        if ($store) {
            $onboardingCompleted = isset($store->settings['onboarding_completed']) && $store->settings['onboarding_completed'] === true;
        }

        if (!$onboardingCompleted) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
