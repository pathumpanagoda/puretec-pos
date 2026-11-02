<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureDefinition extends Model
{
    protected $fillable = [
        'code',
        'name',
        'group',
        'description',
        'icon',
        'sort_order',
        'is_default',
        'is_core',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_core'    => 'boolean',
    ];

    /**
     * Get all feature definitions grouped by category.
     */
    public static function grouped(): array
    {
        return static::orderBy('sort_order')
            ->get()
            ->groupBy('group')
            ->toArray();
    }

    /**
     * Get all available feature codes.
     */
    public static function allCodes(): array
    {
        return static::pluck('code')->toArray();
    }

    /**
     * Seed the default feature definitions.
     */
    public static function seedDefaults(): void
    {
        $features = [
            // Core features (cannot be disabled)
            ['code' => 'dashboard',    'name' => 'Dashboard',           'group' => 'core',     'icon' => 'bi-speedometer2',    'sort_order' => 1,  'is_default' => true,  'is_core' => true],
            ['code' => 'pos',          'name' => 'Point of Sale',       'group' => 'core',     'icon' => 'bi-cart3',           'sort_order' => 2,  'is_default' => true,  'is_core' => true],
            ['code' => 'products',     'name' => 'Products',            'group' => 'core',     'icon' => 'bi-box-seam',        'sort_order' => 3,  'is_default' => true,  'is_core' => true],
            ['code' => 'categories',   'name' => 'Categories',          'group' => 'core',     'icon' => 'bi-grid',            'sort_order' => 4,  'is_default' => true,  'is_core' => true],
            ['code' => 'orders',       'name' => 'Orders / Sales',      'group' => 'core',     'icon' => 'bi-receipt',         'sort_order' => 5,  'is_default' => true,  'is_core' => true],

            // Standard features
            ['code' => 'inventory',    'name' => 'Inventory',           'group' => 'standard', 'icon' => 'bi-clipboard-data',  'sort_order' => 10, 'is_default' => true,  'is_core' => false],
            ['code' => 'customers',    'name' => 'Customers',           'group' => 'standard', 'icon' => 'bi-people',          'sort_order' => 11, 'is_default' => true,  'is_core' => false],
            ['code' => 'suppliers',    'name' => 'Suppliers',           'group' => 'standard', 'icon' => 'bi-truck',           'sort_order' => 12, 'is_default' => true,  'is_core' => false],
            ['code' => 'purchases',    'name' => 'Purchases',           'group' => 'standard', 'icon' => 'bi-bag-check',       'sort_order' => 13, 'is_default' => true,  'is_core' => false],
            ['code' => 'expenses',     'name' => 'Expenses',            'group' => 'standard', 'icon' => 'bi-wallet2',         'sort_order' => 14, 'is_default' => true,  'is_core' => false],
            ['code' => 'incomes',      'name' => 'Other Income',        'group' => 'standard', 'icon' => 'bi-cash-coin',       'sort_order' => 15, 'is_default' => false, 'is_core' => false],
            ['code' => 'reports',      'name' => 'Reports',             'group' => 'standard', 'icon' => 'bi-graph-up',        'sort_order' => 16, 'is_default' => true,  'is_core' => false],
            ['code' => 'settings',     'name' => 'Settings',            'group' => 'standard', 'icon' => 'bi-gear',            'sort_order' => 17, 'is_default' => true,  'is_core' => false],
            ['code' => 'users',        'name' => 'User Management',     'group' => 'standard', 'icon' => 'bi-person-gear',     'sort_order' => 18, 'is_default' => true,  'is_core' => false],

            // Advanced features
            ['code' => 'advanced_reports', 'name' => 'Advanced Reports', 'group' => 'advanced', 'icon' => 'bi-bar-chart-line', 'sort_order' => 20, 'is_default' => false, 'is_core' => false],
            ['code' => 'barcode',      'name' => 'Barcode Management',  'group' => 'advanced', 'icon' => 'bi-upc-scan',        'sort_order' => 21, 'is_default' => true,  'is_core' => false],
            ['code' => 'multi_store',  'name' => 'Multi-Store',         'group' => 'advanced', 'icon' => 'bi-shop',            'sort_order' => 22, 'is_default' => false, 'is_core' => false],
            ['code' => 'multi_register','name'=> 'Multi-Register',      'group' => 'advanced', 'icon' => 'bi-display',         'sort_order' => 23, 'is_default' => false, 'is_core' => false],
            ['code' => 'quotation',    'name' => 'Quotations',          'group' => 'advanced', 'icon' => 'bi-file-earmark-text','sort_order'=> 24, 'is_default' => false, 'is_core' => false],
            ['code' => 'invoice',      'name' => 'Invoicing',           'group' => 'advanced', 'icon' => 'bi-file-earmark-ruled','sort_order'=> 25,'is_default' => false, 'is_core' => false],
            ['code' => 'loyalty',      'name' => 'Loyalty Program',     'group' => 'advanced', 'icon' => 'bi-star',            'sort_order' => 26, 'is_default' => false, 'is_core' => false],

            // Addon features
            ['code' => 'restaurant',   'name' => 'Restaurant / Tables', 'group' => 'addon',    'icon' => 'bi-cup-hot',         'sort_order' => 30, 'is_default' => false, 'is_core' => false],
            ['code' => 'kitchen',      'name' => 'Kitchen Display',     'group' => 'addon',    'icon' => 'bi-display',         'sort_order' => 31, 'is_default' => false, 'is_core' => false],
            ['code' => 'manufacturing','name' => 'Manufacturing',       'group' => 'addon',    'icon' => 'bi-gear-wide-connected','sort_order'=> 32,'is_default' => false, 'is_core' => false],
            ['code' => 'employee',     'name' => 'Employee Management', 'group' => 'addon',    'icon' => 'bi-person-badge',    'sort_order' => 33, 'is_default' => false, 'is_core' => false],

            // Platform features
            ['code' => 'cloud_sync',   'name' => 'Cloud Sync',          'group' => 'platform', 'icon' => 'bi-cloud-arrow-up',  'sort_order' => 40, 'is_default' => true,  'is_core' => false],
            ['code' => 'offline_mode', 'name' => 'Offline Mode',        'group' => 'platform', 'icon' => 'bi-wifi-off',        'sort_order' => 41, 'is_default' => true,  'is_core' => false],
            ['code' => 'mobile_app',   'name' => 'Mobile App Access',   'group' => 'platform', 'icon' => 'bi-phone',           'sort_order' => 42, 'is_default' => false, 'is_core' => false],
        ];

        foreach ($features as $feature) {
            static::updateOrCreate(
                ['code' => $feature['code']],
                $feature
            );
        }
    }
}
