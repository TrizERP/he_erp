<?php

use App\Http\Controllers\api\V1\Auth\AuthController;
use App\Http\Controllers\api\V1\Dashboard\DashboardController;
use App\Http\Controllers\api\V1\Meta\LabelController;
use App\Http\Controllers\api\V1\SchoolSetup\AcademicSectionController;
use App\Http\Controllers\api\V1\SchoolSetup\DivisionController;
use App\Http\Controllers\api\V1\SchoolSetup\StandardController;
use App\Http\Controllers\api\V1\SchoolSetup\SubjectController;
use App\Http\Middleware\Api\ApiAuth;
use App\Http\Middleware\Api\ApiEnvelope;
use App\Http\Middleware\Api\ApiPermission;
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
        Route::get('labels', [LabelController::class, 'index']);

        // Module 1: school setup. Rights come from the Blade menu entry "Create Subject" (subject_master.index).
        Route::prefix('school-setup')->group(function () {
            $menu = 'subject_master.index';
            Route::middleware(ApiPermission::class.":$menu,view")->group(function () {
                Route::get('academic-sections', [AcademicSectionController::class, 'index']);
                Route::get('standards', [StandardController::class, 'index']);
                Route::get('divisions', [DivisionController::class, 'index']);
                Route::get('subjects', [SubjectController::class, 'index']);
            });
            Route::middleware(ApiPermission::class.":$menu,add")->group(function () {
                Route::post('academic-sections', [AcademicSectionController::class, 'store']);
                Route::post('standards', [StandardController::class, 'store']);
                Route::post('divisions', [DivisionController::class, 'store']);
                Route::post('subjects', [SubjectController::class, 'store']);
            });
            Route::put('subjects/{id}', [SubjectController::class, 'update'])->whereNumber('id')->middleware(ApiPermission::class.":$menu,edit");
            Route::delete('subjects/{id}', [SubjectController::class, 'destroy'])->whereNumber('id')->middleware(ApiPermission::class.":$menu,delete");
        });
    });
});
