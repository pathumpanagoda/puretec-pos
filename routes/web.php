<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/login',   [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login',  [AuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Password Reset (No Email Required) ────────────────────────────────────────
Route::get('/forgot-password',           [ForgotPasswordController::class, 'showForgotForm'])->name('password.request')->middleware('guest');
Route::post('/forgot-password',          [ForgotPasswordController::class, 'findUser'])->name('password.find-user')->middleware('guest');
Route::get('/security-question',         [ForgotPasswordController::class, 'showSecurityQuestion'])->name('password.security-question')->middleware('guest');
Route::post('/security-question',        [ForgotPasswordController::class, 'verifySecurityAnswer'])->name('password.verify-security')->middleware('guest');
Route::get('/reset-password/{token}',    [ForgotPasswordController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('/reset-password',           [ForgotPasswordController::class, 'resetPassword'])->name('password.update')->middleware('guest');

// ── Authenticated Routes ───────────────────────────────────────────────────────
// Middleware: auth (login), tenant.scope (auto-scope data), tenant.lock (check payment status)
Route::middleware(['auth', 'tenant.scope', 'tenant.lock'])->group(function () {

    // Dashboard
    Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // POS
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/',                         [POSController::class, 'index'])->name('index');
        Route::get('/customer-display',         [POSController::class, 'customerDisplay'])->name('customer-display');
        Route::get('/products/search',          [POSController::class, 'searchProducts'])->name('products.search');
        Route::get('/customers/search',         [POSController::class, 'searchCustomers'])->name('customers.search');
        Route::post('/sale',                    [POSController::class, 'processSale'])->name('sale');
        Route::post('/bill',                    [POSController::class, 'createBill'])->name('bill');
        Route::get('/pending-payments',         [POSController::class, 'getPendingPayments'])->name('pending-payments');
        Route::post('/approve-payment/{order}', [POSController::class, 'approvePayment'])->name('approve-payment');
        Route::post('/hold',                    [POSController::class, 'holdOrder'])->name('hold');
        Route::get('/held-orders',              [POSController::class, 'getHeldOrders'])->name('held');
        Route::get('/held-orders/{order}/retrieve', [POSController::class, 'retrieveHeldOrder'])->name('held.retrieve');
        Route::delete('/held-orders/{order}',   [POSController::class, 'deleteHeldOrder'])->name('held.delete');
        Route::post('/coupon',                  [POSController::class, 'applyCoupon'])->name('coupon');
        Route::post('/gift-card/validate',      [POSController::class, 'validateGiftCard'])->name('giftcard');
        Route::post('/register/open',           [POSController::class, 'openRegister'])->name('register.open');
        Route::post('/register/{register}/close',[POSController::class, 'closeRegister'])->name('register.close');
    });

    // Products - Custom routes MUST come before resource route
    Route::get('/products/generate-barcode', [ProductController::class, 'generateBarcode'])->name('products.barcode');
    Route::get('/products/generate-sku',     [ProductController::class, 'getNextSku'])->name('products.sku');
    Route::get('/products/suggest-category', [ProductController::class, 'suggestCategory'])->name('products.suggest-category');
    Route::get('/products/search-barcode',   [ProductController::class, 'searchByBarcode'])->name('products.search-barcode');
    Route::get('/products/sub-categories',   [ProductController::class, 'getSubCategories'])->name('products.sub-categories');
    Route::get('/products/search',           [ProductController::class, 'searchProducts'])->name('products.search');
    Route::get('/products/import',           [ProductController::class, 'showImport'])->name('products.import');
    Route::post('/products/import',          [ProductController::class, 'import'])->name('products.import.process');
    Route::get('/products/import/template',  [ProductController::class, 'downloadTemplate'])->name('products.import.template');
    // Product-specific custom routes (before resource)
    Route::get('/products/quick-get/{id}', [ProductController::class, 'getProduct'])->name('products.get');
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
    Route::post('/products/{product}/quick-update', [ProductController::class, 'quickUpdate'])->name('products.quick-update');
    Route::post('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::match(['get', 'post'], '/products/price-tags', [ProductController::class, 'priceTags'])->name('products.price-tags');
    // Resource routes (last)
    Route::resource('products', ProductController::class);

    // Categories
    Route::post('/categories/quick', [CategoryController::class, 'storeQuick'])->name('categories.quick');
    Route::get('/categories/suggest-code', [CategoryController::class, 'suggestCode'])->name('categories.suggest-code');
    Route::resource('categories', CategoryController::class);

    // Customers
    Route::resource('customers', CustomerController::class);

    // Suppliers
    Route::resource('suppliers', SupplierController::class);

    // Orders
    Route::resource('orders', OrderController::class)->only(['index','show','edit','update','destroy']);
    Route::get('/orders/{order}/receipt', [OrderController::class, 'printReceipt'])->name('orders.receipt');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'printInvoice'])->name('orders.invoice');
    Route::post('/orders/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
    Route::post('/orders/bulk-delete', [OrderController::class, 'bulkDelete'])->name('orders.bulk-delete');

    // Purchases
    Route::resource('purchases', PurchaseController::class)->except(['edit','update','destroy']);
    Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');

    // Inventory
    Route::get('/inventory',                        [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/adjust',                [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('/inventory/movements/{product}',    [InventoryController::class, 'movements'])->name('inventory.movements');

    // Expenses
    Route::resource('expenses', ExpenseController::class);

    // Income
    Route::resource('incomes', IncomeController::class);
    Route::post('/incomes/category', [IncomeController::class, 'storeCategory'])->name('incomes.category.store');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',          [ReportController::class, 'index'])->name('index');
        Route::get('/daily',     [ReportController::class, 'daily'])->name('daily');
        Route::get('/sales',     [ReportController::class, 'sales'])->name('sales');
        Route::get('/pos',       [ReportController::class, 'pos'])->name('pos');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/stock-movements', [ReportController::class, 'stockMovements'])->name('stock-movements');
        Route::get('/profit',    [ReportController::class, 'profit'])->name('profit');
        Route::get('/cashflow',  [ReportController::class, 'cashflow'])->name('cashflow');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/expenses',  [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/sessions',  [ReportController::class, 'sessions'])->name('sessions');
        Route::get('/balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/cash-book', [ReportController::class, 'cashBook'])->name('cash-book');
        Route::get('/export',    [ReportController::class, 'export'])->name('export');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',                 [SettingController::class, 'index'])->name('index');
        Route::post('/store',           [SettingController::class, 'updateStore'])->name('store');
        Route::post('/receipt',         [SettingController::class, 'updateReceipt'])->name('receipt');
        Route::post('/devices',         [SettingController::class, 'updateDevices'])->name('devices');
        Route::post('/scanner',         [SettingController::class, 'updateScanner'])->name('scanner');
        Route::post('/cardreader',      [SettingController::class, 'updateCardReader'])->name('cardreader');
        Route::post('/cashdrawer',      [SettingController::class, 'updateCashDrawer'])->name('cashdrawer');
        Route::post('/display',         [SettingController::class, 'updateDisplay'])->name('display');
        Route::post('/pos',             [SettingController::class, 'updatePOS'])->name('pos');
        Route::post('/season-mode',     [SettingController::class, 'updateSeasonMode'])->name('season-mode');
        Route::post('/tax',             [SettingController::class, 'updateTax'])->name('tax');
        Route::post('/inventory',       [SettingController::class, 'updateInventory'])->name('inventory');
        Route::post('/notifications',   [SettingController::class, 'updateNotifications'])->name('notifications');
        Route::get('/backup/download',  [SettingController::class, 'downloadBackup'])->name('backup.download');
        Route::post('/backup/restore',  [SettingController::class, 'restoreBackup'])->name('backup.restore');
        Route::post('/backup/schedule', [SettingController::class, 'scheduleBackup'])->name('backup.schedule');
        Route::post('/reset',           [SettingController::class, 'resetData'])->name('reset');
        Route::post('/users',           [SettingController::class, 'storeUser'])->name('users.store');
    });

    // Users (admin only)
    Route::resource('users', UserController::class)->except(['show']);

    // Profile
    Route::get('/profile',          [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update',  [AuthController::class, 'updateProfile'])->name('profile.update');
});

// API endpoint for dismissing payment reminder
Route::post('/api/dismiss-payment-reminder', function () {
    session(['payment_reminder_dismissed' => true]);
    return response()->json(['success' => true]);
})->middleware('auth');

// Include Nexfloit admin panel routes
require __DIR__.'/nexfloit.php';
