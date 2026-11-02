<?php

namespace App\Http\Controllers\Puretec;

use App\Http\Controllers\Controller;
use App\Models\DeviceRegistration;
use App\Models\Tenant;
use Illuminate\Http\Request;

class DeviceManagementController extends Controller
{
    /**
     * List all devices across tenants.
     */
    public function index(Request $request)
    {
        $query = DeviceRegistration::with(['user:id,name,email', 'store:id,name'])
            ->orderBy('last_seen_at', 'desc');

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $devices = $query->paginate(50);
        $tenants = Tenant::where('is_active', true)->pluck('business_name', 'id');

        return view('puretec.device-management', compact('devices', 'tenants'));
    }

    /**
     * Revoke a device.
     */
    public function revoke(DeviceRegistration $device)
    {
        $device->update(['is_active' => false]);
        return back()->with('success', "Device '{$device->device_name}' has been revoked.");
    }
}
