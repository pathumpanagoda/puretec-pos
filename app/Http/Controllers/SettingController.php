<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use App\Models\Tax;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $store = $user->store;
        $taxes = Tax::where('store_id', $user->store_id)->get();
        $users = User::where('store_id', $user->store_id)->withTrashed()->get();
        return view('settings.index', compact('store', 'taxes', 'users'));
    }

    /**
     * Update store/business information
     */
    public function updateStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $store = Auth::user()->store;
        $data  = $request->only(['name', 'code', 'address', 'city', 'country', 'phone', 'email', 'currency', 'currency_symbol', 'tax_rate']);

        if ($request->hasFile('logo')) {
            if ($store->logo) Storage::disk('public')->delete($store->logo);
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $store->update($data);
        return back()->with('success', 'Business information saved!');
    }

    /**
     * Update receipt settings
     */
    public function updateReceipt(Request $request)
    {
        $store = Auth::user()->store;
        $store->update([
            'receipt_header' => $request->receipt_header,
            'receipt_footer' => $request->receipt_footer,
        ]);

        $settings = $store->settings ?? [];

        // Basic receipt settings - Quick Print format
        $settings['default_print_format'] = $request->default_print_format ?? 'thermal-80';
        $settings['receipt_paper_size'] = $request->receipt_paper_size;
        $settings['receipt_template'] = $request->receipt_template;
        $settings['invoice_prefix'] = $request->invoice_prefix;
        $settings['show_logo_on_receipt'] = $request->has('show_logo_on_receipt');
        $settings['show_tax_breakdown'] = $request->has('show_tax_breakdown');
        $settings['auto_print_receipt'] = $request->has('auto_print_receipt');
        $settings['print_duplicate'] = $request->has('print_duplicate');

        // Print layout settings
        $settings['print_margin_top'] = (int) ($request->print_margin_top ?? 3);
        $settings['print_margin_bottom'] = (int) ($request->print_margin_bottom ?? 3);
        $settings['print_margin_left'] = (int) ($request->print_margin_left ?? 3);
        $settings['print_margin_right'] = (int) ($request->print_margin_right ?? 3);
        $settings['print_font_store_name'] = $request->print_font_store_name ?? '16';
        $settings['print_font_items'] = $request->print_font_items ?? '12';
        $settings['print_font_total'] = $request->print_font_total ?? '16';
        $settings['print_line_spacing'] = $request->print_line_spacing ?? '1.4';
        $settings['print_receipt_width'] = $request->print_receipt_width ?? 'auto';
        $settings['print_divider_style'] = $request->print_divider_style ?? 'dashed';
        $settings['print_colors'] = $request->print_colors ?? 'bw';

        $store->update(['settings' => $settings]);
        return back()->with('success', 'Receipt settings saved!');
    }

    /**
     * Update printer/device settings
     */
    public function updateDevices(Request $request)
    {
        $store = Auth::user()->store;
        $settings = $store->settings ?? [];

        $printers = [];
        foreach ($request->printers ?? [] as $index => $printer) {
            if (!empty($printer['name'])) {
                $printers[] = [
                    'name' => $printer['name'],
                    'type' => $printer['type'] ?? 'usb',
                    'paper_size' => $printer['paper_size'] ?? '80mm',
                    'ip_address' => $printer['ip_address'] ?? '',
                    'port' => $printer['port'] ?? '9100',
                    'usage' => $printer['usage'] ?? 'receipt',
                    'enabled' => isset($printer['enabled']),
                ];
            }
        }

        $settings['printers'] = $printers;
        $store->update(['settings' => $settings]);

        return back()->with('success', 'Printer settings saved!');
    }

    /**
     * Update barcode scanner settings
     */
    public function updateScanner(Request $request)
    {
        $store = Auth::user()->store;
        $settings = $store->settings ?? [];

        $settings['scanner'] = [
            'type' => $request->scanner_type,
            'formats' => $request->barcode_formats ?? [],
            'beep' => $request->has('scanner_beep'),
            'auto_add' => $request->has('scanner_auto_add'),
        ];

        $store->update(['settings' => $settings]);
        return back()->with('success', 'Scanner settings saved!');
    }

    /**
     * Update card reader settings
     */
    public function updateCardReader(Request $request)
    {
        $store = Auth::user()->store;
        $settings = $store->settings ?? [];

        $settings['card_reader'] = [
            'type' => $request->terminal_type,
            'ip' => $request->terminal_ip,
            'merchant_id' => $request->merchant_id,
            'accepted_cards' => $request->accepted_cards ?? [],
        ];

        $store->update(['settings' => $settings]);
        return back()->with('success', 'Card reader settings saved!');
    }

    /**
     * Update cash drawer settings
     */
    public function updateCashDrawer(Request $request)
    {
        $store = Auth::user()->store;
        $settings = $store->settings ?? [];

        $settings['cash_drawer'] = [
            'type' => $request->drawer_type,
            'auto_open' => $request->has('auto_open_drawer'),
            'open_on_card' => $request->has('open_drawer_card'),
        ];

        $store->update(['settings' => $settings]);
        return back()->with('success', 'Cash drawer settings saved!');
    }

    /**
     * Update customer display settings
     */
    public function updateDisplay(Request $request)
    {
        $store = Auth::user()->store;
        $settings = $store->settings ?? [];

        $settings['customer_display'] = [
            'type' => $request->display_type,
            'port' => $request->display_port,
            'show_item_price' => $request->has('show_item_price'),
            'show_total' => $request->has('show_total'),
        ];

        $store->update(['settings' => $settings]);
        return back()->with('success', 'Customer display settings saved!');
    }

    /**
     * Update POS settings
     */
    public function updatePOS(Request $request)
    {
        $store = Auth::user()->store;
        $settings = $store->settings ?? [];

        $settings['pos'] = [
            'decimal_places' => $request->decimal_places,
            'default_payment' => $request->default_payment,
            'order_format' => $request->order_format,
            'default_customer' => $request->default_customer,
            'quick_amounts' => $request->quick_amounts,
            'barcode_mode' => $request->barcode_mode,
            'allow_negative_stock' => $request->has('allow_negative_stock'),
            'require_customer' => $request->has('require_customer'),
            'allow_discounts' => $request->has('allow_discounts'),
            'allow_price_edit' => $request->has('allow_price_edit'),
            'show_stock' => $request->has('show_stock'),
            'confirm_checkout' => $request->has('confirm_checkout'),
            'sound_effects' => $request->has('sound_effects'),
            'hold_orders' => $request->has('hold_orders'),
        ];

        $store->update(['settings' => $settings]);
        return back()->with('success', 'POS settings saved!');
    }

    /**
     * Toggle season mode - all cashiers get full payment access
     */
    public function updateSeasonMode(Request $request)
    {
        $user = Auth::user();

        // Only managers and above can toggle season mode
        if (!$user->isManager()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $store = $user->store;
        $settings = $store->settings ?? [];
        $settings['season_mode'] = $request->boolean('season_mode');
        $store->update(['settings' => $settings]);

        return response()->json([
            'success' => true,
            'season_mode' => $settings['season_mode'],
            'message' => $settings['season_mode']
                ? 'Season mode enabled! All cashiers can process payments.'
                : 'Season mode disabled. Bill-only cashiers cannot process payments.',
        ]);
    }

    /**
     * Update tax settings
     */
    public function updateTax(Request $request)
    {
        $storeId = Auth::user()->store_id;

        foreach ($request->taxes ?? [] as $key => $taxData) {
            if (empty($taxData['name'])) continue;

            $data = [
                'name' => $taxData['name'],
                'rate' => $taxData['rate'] ?? 0,
                'type' => $taxData['type'] ?? 'percentage',
                'apply_to' => $taxData['apply_to'] ?? 'all',
                'is_active' => isset($taxData['is_active']),
            ];

            if (!empty($taxData['id'])) {
                Tax::find($taxData['id'])?->update($data);
            } else {
                Tax::create(array_merge($data, ['store_id' => $storeId]));
            }
        }

        return back()->with('success', 'Tax settings updated!');
    }

    /**
     * Update inventory settings
     */
    public function updateInventory(Request $request)
    {
        $store = Auth::user()->store;
        $settings = $store->settings ?? [];

        $settings['inventory'] = [
            'low_stock_level' => $request->low_stock_level,
            'reorder_level' => $request->reorder_level,
            'stock_method' => $request->stock_method,
            'default_unit' => $request->default_unit,
            'sku_prefix' => $request->sku_prefix,
            'auto_sku' => $request->auto_sku,
            'track_stock_default' => $request->has('track_stock_default'),
            'allow_negative_stock' => $request->has('allow_negative_stock'),
            'auto_deduct_stock' => $request->has('auto_deduct_stock'),
            'stock_alert_email' => $request->has('stock_alert_email'),
            'track_expiry' => $request->has('track_expiry'),
            'batch_tracking' => $request->has('batch_tracking'),
        ];

        $store->update(['settings' => $settings]);
        return back()->with('success', 'Inventory settings saved!');
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $store = Auth::user()->store;
        $settings = $store->settings ?? [];

        $settings['notifications'] = [
            'email_daily_report' => $request->has('email_daily_report'),
            'email_low_stock' => $request->has('email_low_stock'),
            'email_new_order' => $request->has('email_new_order'),
            'notification_email' => $request->notification_email,
            'alert_low_stock' => $request->has('alert_low_stock'),
            'alert_expiring' => $request->has('alert_expiring'),
            'alert_pending_orders' => $request->has('alert_pending_orders'),
            'sound_notifications' => $request->has('sound_notifications'),
            'browser_notifications' => $request->has('browser_notifications'),
        ];

        $store->update(['settings' => $settings]);
        return back()->with('success', 'Notification settings saved!');
    }

    /**
     * Download backup
     */
    public function downloadBackup(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $type = $request->get('type', 'full');

        $data = [
            'exported_at' => now()->toIso8601String(),
            'store_id' => $storeId,
            'type' => $type,
        ];

        if ($type === 'full' || $type === 'products') {
            $data['products'] = Product::where('store_id', $storeId)->get()->toArray();
        }
        if ($type === 'full' || $type === 'customers') {
            $data['customers'] = Customer::where('store_id', $storeId)->get()->toArray();
        }
        if ($type === 'full' || $type === 'orders') {
            $data['orders'] = Order::where('store_id', $storeId)->with('items', 'payments')->get()->toArray();
        }
        if ($type === 'full' || $type === 'settings') {
            $data['store'] = Store::find($storeId)->toArray();
            $data['taxes'] = Tax::where('store_id', $storeId)->get()->toArray();
        }

        $filename = 'pure_pos_backup_' . now()->format('Y-m-d_His') . '.json';
        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    /**
     * Restore backup
     */
    public function restoreBackup(Request $request)
    {
        $request->validate(['backup_file' => 'required|file']);

        $file = $request->file('backup_file');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (!$data || !isset($data['store_id'])) {
            return back()->with('error', 'Invalid backup file format.');
        }

        $storeId = Auth::user()->store_id;

        DB::beginTransaction();
        try {
            if (!empty($data['products'])) {
                foreach ($data['products'] as $product) {
                    unset($product['id']);
                    $product['store_id'] = $storeId;
                    Product::create($product);
                }
            }

            if (!empty($data['customers'])) {
                foreach ($data['customers'] as $customer) {
                    unset($customer['id']);
                    $customer['store_id'] = $storeId;
                    Customer::create($customer);
                }
            }

            DB::commit();
            return back()->with('success', 'Backup restored successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Schedule automatic backup
     */
    public function scheduleBackup(Request $request)
    {
        $store = Auth::user()->store;
        $settings = $store->settings ?? [];

        $settings['backup'] = [
            'schedule' => $request->backup_schedule,
            'time' => $request->backup_time,
            'retention' => $request->backup_retention,
        ];

        $store->update(['settings' => $settings]);
        return back()->with('success', 'Backup schedule saved!');
    }

    /**
     * Reset data - System Reset Feature for clearing test data
     */
    public function resetData(Request $request)
    {
        $user = Auth::user();

        // Only super_admin can reset data
        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Only Super Admin can reset system data.');
        }

        $type = $request->get('type');
        $confirmText = $request->get('confirm_text');
        $storeId = $user->store_id;

        if (!$type) {
            return back()->with('error', 'Invalid reset type.');
        }

        // Require confirmation text for safety
        if ($type === 'full_reset' && $confirmText !== 'RESET ALL DATA') {
            return back()->with('error', 'Please type "RESET ALL DATA" to confirm full system reset.');
        }

        DB::beginTransaction();
        try {
            switch ($type) {
                case 'orders':
                    // Delete order items, payments, then orders
                    DB::table('order_items')->whereIn('order_id', Order::where('store_id', $storeId)->pluck('id'))->delete();
                    DB::table('order_payments')->whereIn('order_id', Order::where('store_id', $storeId)->pluck('id'))->delete();
                    Order::where('store_id', $storeId)->forceDelete();
                    break;

                case 'expenses':
                    \App\Models\Expense::where('store_id', $storeId)->delete();
                    break;

                case 'incomes':
                    \App\Models\Income::where('store_id', $storeId)->delete();
                    break;

                case 'inventory':
                    Product::where('store_id', $storeId)->update(['stock_quantity' => 0]);
                    DB::table('stock_movements')->where('store_id', $storeId)->delete();
                    break;

                case 'customers':
                    Customer::where('store_id', $storeId)->delete();
                    break;

                case 'full_reset':
                    // Complete system reset - delete all transactional data

                    // 1. Delete order related data
                    $orderIds = Order::where('store_id', $storeId)->pluck('id');
                    DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
                    DB::table('order_payments')->whereIn('order_id', $orderIds)->delete();
                    Order::where('store_id', $storeId)->forceDelete();

                    // 2. Delete held orders
                    DB::table('held_orders')->where('store_id', $storeId)->delete();

                    // 3. Delete expenses
                    DB::table('expenses')->where('store_id', $storeId)->delete();

                    // 4. Delete incomes
                    DB::table('incomes')->where('store_id', $storeId)->delete();

                    // 5. Delete stock movements
                    DB::table('stock_movements')->where('store_id', $storeId)->delete();

                    // 6. Delete purchases
                    $purchaseIds = DB::table('purchases')->where('store_id', $storeId)->pluck('id');
                    DB::table('purchase_items')->whereIn('purchase_id', $purchaseIds)->delete();
                    DB::table('purchases')->where('store_id', $storeId)->delete();

                    // 7. Reset register sessions
                    DB::table('registers')->where('store_id', $storeId)->delete();

                    // 8. Reset product stock to 0
                    Product::where('store_id', $storeId)->update(['stock_quantity' => 0]);

                    // 9. Reset customer points and balances
                    Customer::where('store_id', $storeId)->update([
                        'loyalty_points' => 0,
                        'total_purchases' => 0,
                        'balance' => 0
                    ]);

                    break;
            }

            DB::commit();

            $messages = [
                'orders' => 'All orders deleted successfully!',
                'expenses' => 'All expenses deleted successfully!',
                'incomes' => 'All incomes deleted successfully!',
                'inventory' => 'Inventory reset to zero!',
                'customers' => 'All customers deleted successfully!',
                'full_reset' => 'System fully reset! All test data has been cleared.',
            ];

            return redirect()->route('settings.index')->with('success', $messages[$type] ?? 'Data reset successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Reset failed: ' . $e->getMessage());
        }
    }

    /**
     * Store a new user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'username' => 'required|string|unique:users',
            'role'     => 'required|in:admin,manager,cashier,inventory,viewer',
            'password' => 'required|min:6',
        ]);

        User::create([
            'store_id' => Auth::user()->store_id,
            'name'     => $request->name,
            'email'    => $request->email,
            'username' => $request->username,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
            'is_active'=> true,
        ]);

        return back()->with('success', 'User created successfully!');
    }
}
