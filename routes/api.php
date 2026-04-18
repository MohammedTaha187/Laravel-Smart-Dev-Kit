<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
// [GEN-IMPORTS]
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['set.locale'])->group(function () {

    // Auth Routes
    // Social Authentication
    Route::get('{provider}/redirect', [AuthController::class, 'socialRedirect']);
    Route::get('{provider}/callback', [AuthController::class, 'socialCallback']);

    // Register
    Route::post('register', [AuthController::class, 'register']);
    // Login
    Route::post('login', [AuthController::class, 'login']);
    Route::prefix('auth')->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    // Client Routes (Public / Protected API)
    Route::prefix('v1')->group(function () {
        // [GEN-CLIENT-ROUTES]
    });

    // Admin Management Routes
    Route::prefix('admin')->middleware(['auth:api', 'role:admin'])->group(function () {
        // [GEN-ADMIN-ROUTES]
    });
});
