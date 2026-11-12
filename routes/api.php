<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SyncController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\HealthController;

/*
|--------------------------------------------------------------------------
| API Routes — Version 1
|--------------------------------------------------------------------------
|
| These routes serve the mobile app (Flutter), PWA offline sync,
| and any future API consumers. All use Sanctum token authentication.
|
| SECURITY: Every authenticated route derives tenant/store context
| from the authenticated user — NEVER trust client-supplied
| tenant_id, store_id, or user_id.
|
*/

// ── Public (Unauthenticated) ─────────────────────────────────
Route::prefix('v1')->group(function () {

    // Client Identification (Rate limited, for shared mobile apps / PWA)
    Route::middleware('throttle:30,1')->group(function () {
        Route::match(['GET', 'POST'], '/client/identify/{code?}', [ConfigController::class, 'identifyClient']);
    });

    // Authentication (rate limited)
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::post('/auth/register-device', [AuthController::class, 'registerDevice']);
    });

    // Health check (public)
    Route::get('/health', function () {
        return response()->json([
            'status'  => 'ok',
            'version' => config('pos.version', '2.0.0'),
            'time'    => now()->toIso8601String(),
        ]);
    });
});

// ── Authenticated (Token Required & Rate Limited) ────────────
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {

    // ── Auth ─────────────────────────────────────────────────
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // ── Mobile Configuration ─────────────────────────────────
    Route::get('/config', [ConfigController::class, 'index']);

    // ── Sync Endpoints ───────────────────────────────────────
    Route::prefix('sync')->group(function () {
        Route::post('/push', [SyncController::class, 'push']);
        Route::get('/pull', [SyncController::class, 'pull']);
        Route::get('/status', [SyncController::class, 'status']);
    });

    // ── Products ─────────────────────────────────────────────
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{uuid}', [ProductController::class, 'show']);
    Route::get('/products/barcode/{barcode}', [ProductController::class, 'findByBarcode']);

    // ── Categories ───────────────────────────────────────────
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // ── Customers ────────────────────────────────────────────
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{uuid}', [CustomerController::class, 'show']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::match(['PUT', 'PATCH'], '/customers/{uuid}', [CustomerController::class, 'update']);

    // ── Sales ────────────────────────────────────────────────
    Route::post('/sales', [SaleController::class, 'store']);
    Route::get('/sales', [SaleController::class, 'index']);
    Route::get('/sales/{id}', [SaleController::class, 'show']);

    // ── Inventory ────────────────────────────────────────────
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::get('/stock-movements', [InventoryController::class, 'movements']);
    Route::post('/stock-movements', [InventoryController::class, 'store']);

    // ── Devices ──────────────────────────────────────────────
    Route::post('/devices/register', [DeviceController::class, 'register']);
    Route::get('/devices', [DeviceController::class, 'index']);
    Route::post('/devices/{id}/revoke', [DeviceController::class, 'revoke']);

    // ── Health (detailed, authenticated) ─────────────────────
    Route::get('/health/detailed', [HealthController::class, 'detailed']);
});
