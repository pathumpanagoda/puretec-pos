<?php

namespace App\Http\Controllers\Puretec;

use App\Http\Controllers\Controller;
use App\Models\SyncLog;
use App\Models\Tenant;
use App\Models\DeviceRegistration;
use Illuminate\Http\Request;

class SyncMonitorController extends Controller
{
    /**
     * Sync monitoring overview.
     */
    public function index(Request $request)
    {
        $tenants = Tenant::where('is_active', true)
            ->withCount(['stores', 'users'])
            ->get()
            ->map(function ($tenant) {
                // Get sync stats for each tenant
                $tenant->sync_stats = [
                    'last_push' => SyncLog::where('tenant_id', $tenant->id)
                        ->where('direction', 'push')
                        ->latest()
                        ->value('created_at'),
                    'last_pull' => SyncLog::where('tenant_id', $tenant->id)
                        ->where('direction', 'pull')
                        ->latest()
                        ->value('created_at'),
                    'failed_today' => SyncLog::where('tenant_id', $tenant->id)
                        ->where('status', 'failed')
                        ->whereDate('created_at', today())
                        ->count(),
                    'total_today' => SyncLog::where('tenant_id', $tenant->id)
                        ->whereDate('created_at', today())
                        ->count(),
                    'active_devices' => DeviceRegistration::where('tenant_id', $tenant->id)
                        ->where('is_active', true)
                        ->count(),
                ];
                return $tenant;
            });

        return view('puretec.sync-monitor', compact('tenants'));
    }

    /**
     * Detailed sync status for a specific tenant.
     */
    public function show(Tenant $tenant)
    {
        $recentLogs = SyncLog::where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(50);

        $failedLogs = SyncLog::where('tenant_id', $tenant->id)
            ->where('status', 'failed')
            ->latest()
            ->limit(20)
            ->get();

        $devices = DeviceRegistration::where('tenant_id', $tenant->id)
            ->orderBy('last_seen_at', 'desc')
            ->get();

        $stats = [
            'total_syncs'    => SyncLog::where('tenant_id', $tenant->id)->count(),
            'failed_total'   => SyncLog::where('tenant_id', $tenant->id)->where('status', 'failed')->count(),
            'failed_today'   => SyncLog::where('tenant_id', $tenant->id)->where('status', 'failed')->whereDate('created_at', today())->count(),
            'success_today'  => SyncLog::where('tenant_id', $tenant->id)->where('status', 'success')->whereDate('created_at', today())->count(),
            'duplicate_today'=> SyncLog::where('tenant_id', $tenant->id)->where('status', 'duplicate')->whereDate('created_at', today())->count(),
        ];

        return view('puretec.sync-monitor-detail', compact('tenant', 'recentLogs', 'failedLogs', 'devices', 'stats'));
    }

    /**
     * Failed synchronization monitoring across all tenants.
     */
    public function failed(Request $request)
    {
        $query = SyncLog::with(['tenant:id,business_name,code', 'store:id,name', 'user:id,name'])
            ->where('status', 'failed')
            ->latest();

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $failedLogs = $query->paginate(50);
        $tenants = Tenant::where('is_active', true)->pluck('business_name', 'id');
        $entityTypes = SyncLog::where('status', 'failed')->distinct()->pluck('entity_type');

        $stats = [
            'total_failed' => SyncLog::where('status', 'failed')->count(),
            'failed_today' => SyncLog::where('status', 'failed')->whereDate('created_at', today())->count(),
            'conflict_total' => SyncLog::where('status', 'conflict')->count(),
            'affected_tenants' => SyncLog::where('status', 'failed')->distinct('tenant_id')->count('tenant_id'),
        ];

        return view('puretec.sync-monitor-failed', compact('failedLogs', 'tenants', 'entityTypes', 'stats'));
    }
}
