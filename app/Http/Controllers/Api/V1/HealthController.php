<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    /**
     * System health check.
     *
     * GET /api/v1/health/detailed
     *
     * Checks: database, cache, storage, application.
     */
    public function detailed(Request $request)
    {
        $checks = [];
        $healthy = true;

        // Database
        try {
            DB::connection()->getPdo();
            $checks['database'] = ['status' => 'ok', 'message' => 'Connected'];
        } catch (\Throwable $e) {
            $checks['database'] = ['status' => 'error', 'message' => 'Connection failed'];
            $healthy = false;
        }

        // Cache
        try {
            Cache::put('health_check', true, 10);
            $val = Cache::get('health_check');
            $checks['cache'] = ['status' => $val ? 'ok' : 'error', 'message' => $val ? 'Working' : 'Read failed'];
            Cache::forget('health_check');
        } catch (\Throwable $e) {
            $checks['cache'] = ['status' => 'error', 'message' => 'Cache error'];
            $healthy = false;
        }

        // Storage
        try {
            $testFile = 'health_check_' . time() . '.tmp';
            Storage::put($testFile, 'test');
            $exists = Storage::exists($testFile);
            Storage::delete($testFile);
            $checks['storage'] = ['status' => $exists ? 'ok' : 'error', 'message' => $exists ? 'Writable' : 'Not writable'];
        } catch (\Throwable $e) {
            $checks['storage'] = ['status' => 'error', 'message' => 'Storage error'];
            $healthy = false;
        }

        // Application
        $checks['application'] = [
            'status'  => 'ok',
            'version' => config('pos.version', '2.0.0'),
            'env'     => config('app.env'),
            'debug'   => config('app.debug'),
        ];

        // Sync (only if authenticated)
        if ($request->user()) {
            try {
                $pendingSync = DB::table('sync_logs')
                    ->where('status', 'failed')
                    ->where('created_at', '>=', now()->subDay())
                    ->count();

                $checks['sync'] = [
                    'status'       => $pendingSync > 10 ? 'warning' : 'ok',
                    'failed_today' => $pendingSync,
                ];
            } catch (\Throwable $e) {
                $checks['sync'] = ['status' => 'unknown'];
            }
        }

        return response()->json([
            'status'  => $healthy ? 'healthy' : 'unhealthy',
            'checks'  => $checks,
            'time'    => now()->toIso8601String(),
            'version' => config('pos.version', '2.0.0'),
        ], $healthy ? 200 : 503);
    }
}
