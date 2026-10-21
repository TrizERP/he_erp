<?php

use App\Http\Controllers\api\V1\Auth\AuthController;
use App\Http\Controllers\api\V1\Dashboard\DashboardController;
use App\Http\Middleware\Api\ApiAuth;
use App\Http\Middleware\Api\ApiEnvelope;
use Illuminate\Support\Facades\Route;

/*
| Next.js frontend API. Registered by RouteServiceProvider under prefix "api/v1" with the "api" group.
| Every response uses the { success, message, data, meta, errors } envelope (ApiEnvelope middleware).
*/
Route::middleware(ApiEnvelope::class)->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware(ApiAuth::class)->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::get('auth/menu', [AuthController::class, 'menu']);
        Route::get('dashboard', [DashboardController::class, 'summary']);
    });
});
