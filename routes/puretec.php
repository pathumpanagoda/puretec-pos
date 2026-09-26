<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Puretec\AuthController;
use App\Http\Controllers\Puretec\DashboardController;
use App\Http\Controllers\Puretec\TenantController;
use App\Http\Controllers\Puretec\SubscriptionController;
use App\Http\Controllers\Puretec\PaymentController;
use App\Http\Controllers\Puretec\UserController;
use App\Http\Controllers\Puretec\PasswordResetController;

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

        // Tenant Management
        Route::resource('tenants', TenantController::class);
        Route::post('tenants/{tenant}/lock', [TenantController::class, 'lock'])->name('tenants.lock');
        Route::post('tenants/{tenant}/unlock', [TenantController::class, 'unlock'])->name('tenants.unlock');
        Route::post('tenants/{tenant}/create-subscription', [TenantController::class, 'createSubscription'])->name('tenants.create-subscription');

        // Subscription Management
        Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('subscriptions/{subscription}/record-payment', [SubscriptionController::class, 'showRecordPayment'])->name('subscriptions.record-payment');
        Route::post('subscriptions/{subscription}/record-payment', [SubscriptionController::class, 'recordPayment'])->name('subscriptions.record-payment.store');
        Route::post('subscriptions/generate', [SubscriptionController::class, 'generateMonthly'])->name('subscriptions.generate');

        // Payment History
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

        // User Management (all business users)
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
    });

    // Stop impersonation (outside auth middleware)
    Route::post('stop-impersonate', [UserController::class, 'stopImpersonate'])->name('stop-impersonate');
});
