<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Api\AdminProductApiController;
use App\Http\Controllers\Api\AdminCustomerApiController;
use App\Http\Controllers\Api\AdminOrderApiController;
use App\Http\Controllers\Api\AdminIntegrationLogApiController;
use App\Http\Middleware\EnsureNonDemoAdmin;

Route::prefix('admin')->middleware(['web'])->group(function () {
    // Auth
    Route::post('/login', [AuthController::class, 'apiLogin']);
    Route::post('/login/supabase', [AuthController::class, 'loginWithSupabase']);
    Route::post('/logout', [AuthController::class, 'apiLogout']);
    Route::get('/me', [AuthController::class, 'apiMe']);

    // Protected API Endpoints
    Route::middleware('auth:admin')->group(function () {
        // Products
        Route::get('/products', [AdminProductApiController::class, 'index']);
        Route::post('/products', [AdminProductApiController::class, 'store']);
        Route::get('/products/{product}', [AdminProductApiController::class, 'show']);
        Route::put('/products/{product}', [AdminProductApiController::class, 'update']);
        Route::patch('/products/{product}/status', [AdminProductApiController::class, 'toggleStatus']);
        Route::delete('/products/{product}', [AdminProductApiController::class, 'destroy'])
            ->middleware(EnsureNonDemoAdmin::class);

        // Customers
        Route::get('/customers', [AdminCustomerApiController::class, 'index']);
        Route::get('/customers/{id}', [AdminCustomerApiController::class, 'show']);

        // Orders
        Route::get('/orders', [AdminOrderApiController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderApiController::class, 'show']);
        Route::patch('/orders/{order}/status', [AdminOrderApiController::class, 'updateStatus']);

        // Integration Logs
        Route::get('/integration-logs', [AdminIntegrationLogApiController::class, 'index']);
    });
});
