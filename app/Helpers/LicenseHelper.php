<?php

namespace App\Helpers;

use App\Models\Store;
use App\Models\Setting;

class LicenseHelper
{
    /**
     * Get the unique hardware ID (Motherboard UUID) on Windows.
     * Fallback to a hash based on hostname and CPU info if not Windows.
     */
    public static function getMachineId()
    {
        $uuid = null;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Retrieve UUID using wmic on Windows
            $output = shell_exec('wmic csproduct get uuid 2>&1');
            if ($output) {
                $lines = array_filter(array_map('trim', explode("\n", $output)));
                foreach ($lines as $line) {
                    if ($line !== 'UUID' && !empty($line) && strpos($line, 'wmic') === false) {
                        $uuid = $line;
                        break;
                    }
                }
            }
        }

        // If UUID detection failed or not on Windows, generate a fallback based on system info
        if (!$uuid || strpos(strtolower($uuid), 'error') !== false || strlen($uuid) < 10) {
            $uuid = md5(gethostname() . php_uname() . (getenv('PROCESSOR_IDENTIFIER') ?: ''));
        }

        return trim($uuid);
    }

    /**
     * Generate the license signature bound to the machine and license key.
     */
    public static function generateSignature($licenseKey, $machineId)
    {
        return sha1($machineId . $licenseKey . 'nexfloit-pos-salt-key-2026');
    }

    /**
     * Verify if the local license is active and valid for the current hardware.
     */
    public static function verifyLicense()
    {
        $store = Store::first();
        if (!$store) {
            return false;
        }

        $settings = $store->settings ?? [];
        $isActivated = $settings['license_activated'] ?? false;
        $storedKey = $settings['license_key'] ?? null;
        $storedSig = $settings['license_signature'] ?? null;

        if (!$isActivated || !$storedKey || !$storedSig) {
            return false;
        }

        // Generate expected signature for this machine
        $currentMachineId = self::getMachineId();
        $expectedSig = self::generateSignature($storedKey, $currentMachineId);

        return hash_equals($expectedSig, $storedSig);
    }
}
