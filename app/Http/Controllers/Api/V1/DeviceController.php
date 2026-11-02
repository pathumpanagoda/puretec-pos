<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeviceRegistration;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    protected AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    /**
     * Register a device.
     *
     * POST /api/v1/devices/register
     */
    public function register(Request $request)
    {
        $request->validate([
            'device_id'   => 'required|string|max:64',
            'device_name' => 'nullable|string|max:255',
            'device_type' => 'required|in:web,pwa,mobile_android,mobile_ios',
            'app_version' => 'nullable|string|max:20',
            'os_info'     => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        $device = DeviceRegistration::updateOrCreate(
            ['device_id' => $request->device_id],
            [
                'tenant_id'    => $user->tenant_id,
                'store_id'     => $user->store_id,
                'user_id'      => $user->id,
                'device_name'  => $request->device_name ?? 'Unknown',
                'device_type'  => $request->device_type,
                'app_version'  => $request->app_version,
                'os_info'      => $request->os_info,
                'last_seen_at' => now(),
                'is_active'    => true,
            ]
        );

        $this->auditLog->log('device.register', "Device registered: {$request->device_id}");

        return response()->json([
            'success' => true,
            'data'    => [
                'device_id'   => $device->device_id,
                'device_name' => $device->device_name,
                'registered'  => true,
            ],
        ]);
    }

    /**
     * List registered devices.
     *
     * GET /api/v1/devices
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $devices = DeviceRegistration::where('store_id', $user->store_id)
            ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->orderBy('last_seen_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $devices,
        ]);
    }

    /**
     * Revoke a device.
     *
     * POST /api/v1/devices/{id}/revoke
     */
    public function revoke(Request $request, $id)
    {
        $user = $request->user();

        $device = DeviceRegistration::where('id', $id)
            ->where('store_id', $user->store_id)
            ->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device not found.'], 404);
        }

        $device->update(['is_active' => false]);

        $this->auditLog->logCritical(
            'device.revoke',
            "Device revoked: {$device->device_id}",
            'DeviceRegistration',
            $device->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Device revoked successfully.',
        ]);
    }
}
