<?php

namespace App\Services;

use App\Models\ClientFeature;
use App\Models\FeatureDefinition;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class FeatureService
{
    /**
     * Cache key prefix for tenant features.
     */
    protected const CACHE_PREFIX = 'tenant_features_';
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Check if a feature is enabled for a tenant.
     *
     * In a per-client deployment (separate DB per client), the tenant_id check
     * becomes optional. If no tenant_id is provided, we check by feature code only.
     */
    public function isEnabled(?int $tenantId, string $featureCode): bool
    {
        // Core features are always enabled
        $definition = $this->getDefinition($featureCode);
        if ($definition && $definition['is_core']) {
            return true;
        }

        // If no tenant (per-client deployment without tenant table), check default
        if (!$tenantId) {
            return $definition ? $definition['is_default'] : false;
        }

        // Check client-specific feature toggle
        $features = $this->getTenantFeatures($tenantId);
        if (isset($features[$featureCode])) {
            return $features[$featureCode];
        }

        // Fall back to default
        return $definition ? $definition['is_default'] : false;
    }

    /**
     * Get all enabled feature codes for a tenant.
     */
    public function getEnabledFeatures(?int $tenantId): array
    {
        $features = $this->getTenantFeatures($tenantId);
        $definitions = $this->getAllDefinitions();
        $enabled = [];

        foreach ($definitions as $def) {
            $code = $def['code'];
            if ($def['is_core']) {
                $enabled[] = $code;
            } elseif (isset($features[$code])) {
                if ($features[$code]) {
                    $enabled[] = $code;
                }
            } elseif ($def['is_default']) {
                $enabled[] = $code;
            }
        }

        return $enabled;
    }

    /**
     * Get feature status map (code => bool) for a tenant.
     */
    public function getFeatureMap(?int $tenantId): array
    {
        $definitions = $this->getAllDefinitions();
        $features = $this->getTenantFeatures($tenantId);
        $map = [];

        foreach ($definitions as $def) {
            $code = $def['code'];
            if ($def['is_core']) {
                $map[$code] = true;
            } elseif (isset($features[$code])) {
                $map[$code] = $features[$code];
            } else {
                $map[$code] = $def['is_default'];
            }
        }

        return $map;
    }

    /**
     * Enable a feature for a tenant.
     */
    public function enableFeature(int $tenantId, string $featureCode): void
    {
        ClientFeature::updateOrCreate(
            ['tenant_id' => $tenantId, 'feature_code' => $featureCode],
            ['is_enabled' => true, 'enabled_at' => now(), 'disabled_at' => null]
        );

        $this->clearCache($tenantId);
    }

    /**
     * Disable a feature for a tenant.
     */
    public function disableFeature(int $tenantId, string $featureCode): void
    {
        // Cannot disable core features
        $definition = $this->getDefinition($featureCode);
        if ($definition && $definition['is_core']) {
            return;
        }

        ClientFeature::updateOrCreate(
            ['tenant_id' => $tenantId, 'feature_code' => $featureCode],
            ['is_enabled' => false, 'disabled_at' => now()]
        );

        $this->clearCache($tenantId);
    }

    /**
     * Bulk update features for a tenant.
     *
     * @param int   $tenantId
     * @param array $features ['feature_code' => true/false, ...]
     */
    public function updateFeatures(int $tenantId, array $features): void
    {
        foreach ($features as $code => $enabled) {
            if ($enabled) {
                $this->enableFeature($tenantId, $code);
            } else {
                $this->disableFeature($tenantId, $code);
            }
        }
    }

    /**
     * Initialize default features for a new tenant.
     */
    public function initializeForTenant(int $tenantId): void
    {
        $definitions = $this->getAllDefinitions();

        foreach ($definitions as $def) {
            ClientFeature::updateOrCreate(
                ['tenant_id' => $tenantId, 'feature_code' => $def['code']],
                [
                    'is_enabled'  => $def['is_default'] || $def['is_core'],
                    'enabled_at'  => ($def['is_default'] || $def['is_core']) ? now() : null,
                ]
            );
        }

        $this->clearCache($tenantId);
    }

    /**
     * Get cached tenant features map (code => bool).
     */
    protected function getTenantFeatures(?int $tenantId): array
    {
        if (!$tenantId) {
            return [];
        }

        return Cache::remember(
            self::CACHE_PREFIX . $tenantId,
            self::CACHE_TTL,
            function () use ($tenantId) {
                return ClientFeature::where('tenant_id', $tenantId)
                    ->pluck('is_enabled', 'feature_code')
                    ->map(fn($val) => (bool) $val)
                    ->toArray();
            }
        );
    }

    /**
     * Get a feature definition by code.
     */
    protected function getDefinition(string $code): ?array
    {
        $definitions = $this->getAllDefinitions();
        foreach ($definitions as $def) {
            if ($def['code'] === $code) {
                return $def;
            }
        }
        return null;
    }

    /**
     * Get all feature definitions (cached).
     */
    protected function getAllDefinitions(): array
    {
        return Cache::remember('feature_definitions', self::CACHE_TTL, function () {
            return FeatureDefinition::orderBy('sort_order')->get()->toArray();
        });
    }

    /**
     * Clear the tenant's feature cache.
     */
    public function clearCache(?int $tenantId = null): void
    {
        if ($tenantId) {
            Cache::forget(self::CACHE_PREFIX . $tenantId);
        }
        Cache::forget('feature_definitions');
    }
}
