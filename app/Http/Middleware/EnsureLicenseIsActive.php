<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\LicenseHelper;

class EnsureLicenseIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isLicenseRoute = $request->is('license*');
        $isApiOrAjax = $request->is('api/*') || $request->ajax() || $request->wantsJson();

        // Check if current system holds a valid license bound to this machine
        $isLicensed = LicenseHelper::verifyLicense();

        if (!$isLicensed) {
            // Redirect to license activation if not on license or API routes
            if (!$isLicenseRoute && !$isApiOrAjax) {
                return redirect()->route('license.activate');
            }
        } else {
            // If already licensed and attempting to visit license activation, redirect to dashboard/home
            if ($isLicenseRoute) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
