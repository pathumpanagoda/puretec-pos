<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Store;
use App\Models\User;
use App\Models\Category;
use App\Models\Tax;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\ExpenseCategory;
use App\Models\Discount;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default store
        $store = Store::create([
            'name'           => 'Pure POS - Main Store',
            'code'           => 'MAIN',
            'address'        => 'No. 1, Main Street',
            'city'           => 'Colombo',
            'country'        => 'Sri Lanka',
            'phone'          => '+94 11 000 0000',
            'email'          => 'info@purepos.lk',
            'currency'       => 'LKR',
            'currency_symbol'=> 'Rs.',
            'tax_rate'       => 0,
            'receipt_header' => "Pure POS\nNo. 1, Main Street, Colombo",
            'receipt_footer' => 'Thank you for your business!',
        ]);

        // Create Super Admin
        User::create([
            'store_id' => $store->id,
            'name'     => 'Super Admin',
            'email'    => 'admin@purepos.lk',
            'username' => 'admin',
            'role'     => 'super_admin',
            'password' => Hash::make('admin123'),
            'pin'      => '1234',
            'is_active'=> true,
        ]);

        // Create Cashier
        User::create([
            'store_id' => $store->id,
            'name'     => 'Cashier One',
            'email'    => 'cashier@purepos.lk',
            'username' => 'cashier',
            'role'     => 'cashier',
            'password' => Hash::make('cashier123'),
            'pin'      => '5678',
            'is_active'=> true,
        ]);

        // Create Manager
        User::create([
            'store_id' => $store->id,
            'name'     => 'Store Manager',
            'email'    => 'manager@purepos.lk',
            'username' => 'manager',
            'role'     => 'manager',
            'password' => Hash::make('manager123'),
            'pin'      => '9999',
            'is_active'=> true,
        ]);

        // Create Categories
        $categories = [
            ['name' => 'Electronics', 'color' => '#1a6b9a', 'icon' => 'bi-cpu'],
            ['name' => 'Clothing', 'color' => '#2e7d32', 'icon' => 'bi-bag'],
            ['name' => 'Food & Beverages', 'color' => '#f57c00', 'icon' => 'bi-cup-straw'],
            ['name' => 'Health & Beauty', 'color' => '#c62828', 'icon' => 'bi-heart-pulse'],
            ['name' => 'Home & Garden', 'color' => '#6a1b9a', 'icon' => 'bi-house'],
            ['name' => 'Sports', 'color' => '#00838f', 'icon' => 'bi-trophy'],
            ['name' => 'Books & Stationery', 'color' => '#4e342e', 'icon' => 'bi-book'],
            ['name' => 'Toys & Games', 'color' => '#e65100', 'icon' => 'bi-controller'],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, ['store_id' => $store->id]));
        }

        // Create Taxes
        Tax::create(['store_id' => $store->id, 'name' => 'VAT 15%', 'rate' => 15, 'type' => 'percentage', 'is_inclusive' => false]);
        Tax::create(['store_id' => $store->id, 'name' => 'Service Tax 10%', 'rate' => 10, 'type' => 'percentage', 'is_inclusive' => false]);
        Tax::create(['store_id' => $store->id, 'name' => 'Tax Exempt', 'rate' => 0, 'type' => 'percentage', 'is_inclusive' => false]);

        // Create Customer Groups
        $generalGroup = CustomerGroup::create([
            'store_id'      => $store->id,
            'name'          => 'General',
            'discount_rate' => 0,
            'min_purchase'  => 0,
        ]);
        CustomerGroup::create([
            'store_id'      => $store->id,
            'name'          => 'Wholesale',
            'discount_rate' => 10,
            'min_purchase'  => 50000,
        ]);
        CustomerGroup::create([
            'store_id'      => $store->id,
            'name'          => 'VIP',
            'discount_rate' => 15,
            'min_purchase'  => 0,
        ]);

        // Create Walk-in Customer
        Customer::create([
            'store_id'          => $store->id,
            'customer_group_id' => $generalGroup->id,
            'name'              => 'Walk-in Customer',
            'phone'             => '000-000-0000',
            'city'              => 'Colombo',
            'is_active'         => true,
        ]);

        // Sample Products
        $catIds = Category::where('store_id', $store->id)->pluck('id')->toArray();
        $sampleProducts = [
            ['name' => 'iPhone 15 Pro', 'sku' => 'ELEC-001', 'barcode' => '8901234567890', 'cost_price' => 150000, 'selling_price' => 185000, 'stock_quantity' => 25, 'unit' => 'pcs'],
            ['name' => 'Samsung Galaxy S24', 'sku' => 'ELEC-002', 'barcode' => '8901234567891', 'cost_price' => 120000, 'selling_price' => 145000, 'stock_quantity' => 30, 'unit' => 'pcs'],
            ['name' => 'Laptop HP ProBook', 'sku' => 'ELEC-003', 'barcode' => '8901234567892', 'cost_price' => 80000, 'selling_price' => 99000, 'stock_quantity' => 15, 'unit' => 'pcs'],
            ['name' => 'Wireless Headphones', 'sku' => 'ELEC-004', 'barcode' => '8901234567893', 'cost_price' => 3500, 'selling_price' => 5500, 'stock_quantity' => 50, 'unit' => 'pcs'],
            ['name' => 'USB-C Cable 2m', 'sku' => 'ELEC-005', 'barcode' => '8901234567894', 'cost_price' => 250, 'selling_price' => 450, 'stock_quantity' => 200, 'unit' => 'pcs'],
            ['name' => 'Coca-Cola 330ml', 'sku' => 'BEV-001', 'barcode' => '4902102142342', 'cost_price' => 55, 'selling_price' => 90, 'stock_quantity' => 500, 'unit' => 'can'],
            ['name' => 'Mineral Water 500ml', 'sku' => 'BEV-002', 'barcode' => '4902102142343', 'cost_price' => 30, 'selling_price' => 60, 'stock_quantity' => 1000, 'unit' => 'bottle'],
            ['name' => 'Panadol 500mg x10', 'sku' => 'MED-001', 'barcode' => '5010113804019', 'cost_price' => 45, 'selling_price' => 75, 'stock_quantity' => 300, 'unit' => 'strip'],
        ];

        foreach ($sampleProducts as $product) {
            Product::create(array_merge($product, [
                'store_id'    => $store->id,
                'category_id' => $catIds[array_rand($catIds)],
                'min_stock'   => 5,
                'reorder_level'=> 10,
                'is_active'   => true,
                'track_stock' => true,
            ]));
        }

        // Create Expense Categories
        $expCats = ['Rent', 'Utilities', 'Salaries', 'Maintenance', 'Marketing', 'Miscellaneous'];
        foreach ($expCats as $cat) {
            ExpenseCategory::create(['store_id' => $store->id, 'name' => $cat]);
        }

        // Create Sample Discount
        Discount::create([
            'store_id'   => $store->id,
            'name'       => 'Welcome Discount 5%',
            'code'       => 'WELCOME5',
            'type'       => 'percentage',
            'value'      => 5,
            'min_purchase'=> 1000,
            'is_active'  => true,
        ]);

        // Seed Nexfloit Platform Admin
        $this->call(PlatformUserSeeder::class);

        $this->command->info('✅ Pure POS database seeded successfully!');
        $this->command->info('📧 Admin Login: admin@purepos.lk / admin123');
        $this->command->info('📧 Cashier Login: cashier@purepos.lk / cashier123');
        $this->command->info('🔧 Nexfloit Admin: admin@nexfloit.com / admin123');
    }
}
