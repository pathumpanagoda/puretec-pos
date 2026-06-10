<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check() || !auth()->user()->canAccess($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Permission denied'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'You do not have permission to access this area.');
        }
        return $next($request);
    }
}
