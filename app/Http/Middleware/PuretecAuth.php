<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PuretecAuth
{
    /**
     * Handle Puretec admin panel authentication.
     * Ensures only platform users can access admin routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if platform user is logged in
        if (!session()->has('puretec_user_id')) {
            return redirect()->route('puretec.login')
                ->with('error', 'Please login to access the admin panel.');
        }

        // Get platform user from session
        $platformUser = \App\Models\PlatformUser::find(session('puretec_user_id'));

        // Validate user exists and is active
        if (!$platformUser || !$platformUser->is_active) {
            session()->forget('puretec_user_id');
            return redirect()->route('puretec.login')
                ->with('error', 'Your account is inactive. Please contact support.');
        }

        // Share platform user with all views
        view()->share('platformUser', $platformUser);

        // Store in request for controller access
        $request->merge(['platformUser' => $platformUser]);

        return $next($request);
    }
}
