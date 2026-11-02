<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Puretec\AuthController;
use App\Http\Controllers\Puretec\DashboardController;
use App\Http\Controllers\Puretec\TenantController;
use App\Http\Controllers\Puretec\SubscriptionController;
use App\Http\Controllers\Puretec\PaymentController;
use App\Http\Controllers\Puretec\UserController;
use App\Http\Controllers\Puretec\PasswordResetController;
use App\Http\Controllers\Puretec\SyncMonitorController;
use App\Http\Controllers\Puretec\AuditLogController;
use App\Http\Controllers\Puretec\DeviceManagementController;

/*
|--------------------------------------------------------------------------
| Puretec Admin Panel Routes
|--------------------------------------------------------------------------
|
| Routes for the Puretec platform administration panel.
| These are separate from the main POS application routes.
|
*/

Route::prefix('puretec')->name('puretec.')->group(function () {

    // Authentication Routes (no middleware)
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Password Reset Routes (no middleware)
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('forgot-password');
    Route::post('/forgot-password', [PasswordResetController::class, 'findUser']);
    Route::get('/security-question', [PasswordResetController::class, 'showSecurityQuestion'])->name('security-question');
    Route::post('/security-question', [PasswordResetController::class, 'verifySecurityAnswer']);
    Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])->name('reset-password.form');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('reset-password');

    // Protected Routes (require PuretecAuth middleware)
    Route::middleware('puretec.auth')->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // ── Client Management (Phase 8) ──────────────────────
        Route::resource('tenants', TenantController::class);
        Route::post('tenants/{tenant}/lock', [TenantController::class, 'lock'])->name('tenants.lock');
        Route::post('tenants/{tenant}/unlock', [TenantController::class, 'unlock'])->name('tenants.unlock');
        Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
        Route::post('tenants/{tenant}/create-subscription', [TenantController::class, 'createSubscription'])->name('tenants.create-subscription');

        // Feature Management (per tenant)
        Route::get('tenants/{tenant}/features', [TenantController::class, 'features'])->name('tenants.features');
        Route::post('tenants/{tenant}/features', [TenantController::class, 'updateFeatures'])->name('tenants.features.update');

        // Client Provisioning (Phase 9)
        Route::get('tenants/{tenant}/deployment', [TenantController::class, 'deployment'])->name('tenants.deployment');

        // Subscription Management
        Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('subscriptions/{subscription}/record-payment', [SubscriptionController::class, 'showRecordPayment'])->name('subscriptions.record-payment');
        Route::post('subscriptions/{subscription}/record-payment', [SubscriptionController::class, 'recordPayment'])->name('subscriptions.record-payment.store');
        Route::post('subscriptions/generate', [SubscriptionController::class, 'generateMonthly'])->name('subscriptions.generate');

        // Payment History
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

        // User Management (Phase 8)
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('users/{user}/generate-password', [UserController::class, 'generatePassword'])->name('users.generate-password');
        Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
        Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        // Password Reset Requests Management
        Route::get('password-reset-requests', [DashboardController::class, 'passwordResetRequests'])->name('password-reset-requests.index');
        Route::post('password-reset-requests/{id}/approve', [DashboardController::class, 'approvePasswordReset'])->name('password-reset-requests.approve');
        Route::post('password-reset-requests/{id}/reject', [DashboardController::class, 'rejectPasswordReset'])->name('password-reset-requests.reject');

        // ── Sync Monitoring (Phase 8) ────────────────────────
        Route::prefix('sync-monitor')->name('sync-monitor.')->group(function () {
            Route::get('/', [SyncMonitorController::class, 'index'])->name('index');
            Route::get('/{tenant}', [SyncMonitorController::class, 'show'])->name('show');
        });

        // ── Device Management (Phase 8) ──────────────────────
        Route::prefix('device-management')->name('devices.')->group(function () {
            Route::get('/', [DeviceManagementController::class, 'index'])->name('index');
            Route::post('/{device}/revoke', [DeviceManagementController::class, 'revoke'])->name('revoke');
        });

        // ── Audit Log Viewer (Phase 8) ───────────────────────
        Route::prefix('audit-log')->name('audit-log.')->group(function () {
            Route::get('/', [AuditLogController::class, 'index'])->name('index');
        });
    });

    // Stop impersonation (outside auth middleware)
    Route::post('stop-impersonate', [UserController::class, 'stopImpersonate'])->name('stop-impersonate');
});
