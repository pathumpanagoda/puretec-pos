<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Helpers\LicenseHelper;
use App\Models\Store;

class LicenseController extends Controller
{
    /**
     * Show the license activation form.
     */
    public function showActivate()
    {
        if (LicenseHelper::verifyLicense()) {
            return redirect()->route('dashboard');
        }

        $machineId = LicenseHelper::getMachineId();
        return view('auth.license', compact('machineId'));
    }

    /**
     * Process license activation.
     */
    public function activate(Request $request)
    {
        if (LicenseHelper::verifyLicense()) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'license_key' => 'required|string|min:10|max:100',
        ]);

        $licenseKey = strtoupper(trim($request->license_key));
        $machineId = LicenseHelper::getMachineId();

        // 1. Startup phase pre-defined local keys (no server required)
        $validKeys = [
            'DEV-TEST-LICENSE-KEY-2026',
            'PURE-POS-KEY-2026-A1B2',
            'PURE-POS-KEY-2026-C3D4',
            'PURE-POS-KEY-2026-E5F6',
            'PURE-POS-KEY-2026-G7H8',
            'PURE-POS-KEY-2026-I9J0'
        ];

        if (in_array($licenseKey, $validKeys)) {
            $this->saveLicenseActivation($licenseKey, $machineId);
            return redirect()->route('setup.index')->with('success', 'License activated successfully! Please complete setup.');
        }

        // 2. Fallback to Online Activation (only if LICENSE_SERVER_URL is configured in .env)
        $serverUrl = env('LICENSE_SERVER_URL');
        if ($serverUrl) {
            try {
                $response = Http::timeout(15)->post($serverUrl, [
                    'license_key'  => $licenseKey,
                    'machine_id'   => $machineId,
                    'product_name' => 'Pure POS',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['success']) && $data['success'] === true) {
                        $this->saveLicenseActivation($licenseKey, $machineId);
                        return redirect()->route('setup.index')->with('success', 'License activated successfully! Please complete setup.');
                    }

                    $errorMsg = $data['message'] ?? 'Invalid license key or activation failed.';
                    return back()->withErrors(['license_key' => $errorMsg])->withInput();
                }

                return back()->withErrors(['license_key' => 'Activation server returned an error (' . $response->status() . '). Please contact support.'])->withInput();

            } catch (\Exception $e) {
                return back()->withErrors(['license_key' => 'Could not connect to the activation server. Please check your internet connection and try again.'])->withInput();
            }
        }

        // Default error if server is not set up and key is invalid
        return back()->withErrors(['license_key' => 'Invalid license key. Please check the key or contact customer support.'])->withInput();
    }

    /**
     * Helper to save active license settings bound to current machine.
     */
    protected function saveLicenseActivation($licenseKey, $machineId)
    {
        $store = Store::first();
        if (!$store) {
            // Create fallback default store if somehow missing
            $store = Store::create([
                'name' => 'Pure POS - Main Store',
                'code' => 'MAIN',
            ]);
        }

        $settings = $store->settings ?? [];
        $settings['license_activated'] = true;
        $settings['license_key'] = $licenseKey;
        $settings['license_signature'] = LicenseHelper::generateSignature($licenseKey, $machineId);

        $store->update(['settings' => $settings]);
    }
}
