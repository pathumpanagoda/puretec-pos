<?php

namespace App\Services;

use App\Models\Store;
use App\Models\Category;
use App\Models\ExpenseCategory;
use App\Models\CustomerGroup;
use App\Models\Customer;
use App\Models\Tax;
use Illuminate\Support\Str;

class TenantSetupService
{
    /**
     * Set up all default data for a new store.
     * This includes categories, subcategories, expense categories,
     * customer groups, taxes, and a walk-in customer.
     */
    public function setupStoreDefaults(Store $store): void
    {
        $this->createCategories($store);
        $this->createExpenseCategories($store);
        $this->createCustomerGroups($store);
        $this->createTaxes($store);
        $this->createWalkInCustomer($store);
    }

    /**
     * Create default categories and subcategories for a store.
     */
    protected function createCategories(Store $store): void
    {
        $categories = config('tenant_defaults.categories', []);

        foreach ($categories as $index => $categoryData) {
            // Create parent category
            $parent = Category::create([
                'store_id' => $store->id,
                'parent_id' => null,
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'code' => $categoryData['code'],
                'color' => $categoryData['color'],
                'icon' => $categoryData['icon'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);

            // Create child categories (subcategories)
            if (!empty($categoryData['children'])) {
                foreach ($categoryData['children'] as $childIndex => $childData) {
                    Category::create([
                        'store_id' => $store->id,
                        'parent_id' => $parent->id,
                        'name' => $childData['name'],
                        'slug' => Str::slug($childData['name']),
                        'code' => $childData['code'],
                        'color' => $childData['color'],
                        'icon' => $childData['icon'],
                        'sort_order' => $childIndex + 1,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Create default expense categories for a store.
     */
    protected function createExpenseCategories(Store $store): void
    {
        $categories = config('tenant_defaults.expense_categories', []);

        foreach ($categories as $name) {
            ExpenseCategory::create([
                'store_id' => $store->id,
                'name' => $name,
            ]);
        }
    }

    /**
     * Create default customer groups for a store.
     */
    protected function createCustomerGroups(Store $store): void
    {
        $groups = config('tenant_defaults.customer_groups', []);

        foreach ($groups as $groupData) {
            CustomerGroup::create([
                'store_id' => $store->id,
                'name' => $groupData['name'],
                'discount_rate' => $groupData['discount_rate'],
                'min_purchase' => $groupData['min_purchase'],
            ]);
        }
    }

    /**
     * Create default taxes for a store.
     */
    protected function createTaxes(Store $store): void
    {
        $taxes = config('tenant_defaults.taxes', []);

        foreach ($taxes as $taxData) {
            Tax::create([
                'store_id' => $store->id,
                'name' => $taxData['name'],
                'rate' => $taxData['rate'],
                'type' => $taxData['type'],
                'is_inclusive' => $taxData['is_inclusive'],
            ]);
        }
    }

    /**
     * Create a default walk-in customer for a store.
     */
    protected function createWalkInCustomer(Store $store): void
    {
        // Get the General customer group
        $generalGroup = CustomerGroup::where('store_id', $store->id)
            ->where('name', 'General')
            ->first();

        Customer::create([
            'store_id' => $store->id,
            'customer_group_id' => $generalGroup?->id,
            'name' => 'Walk-in Customer',
            'phone' => '000-000-0000',
            'city' => $store->city ?? 'N/A',
            'is_active' => true,
        ]);
    }
}
