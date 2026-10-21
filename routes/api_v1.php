<?php

use App\Http\Controllers\api\V1\Auth\AuthController;
use App\Http\Controllers\api\V1\Dashboard\DashboardController;
use App\Http\Controllers\api\V1\Meta\LabelController;
use App\Http\Controllers\api\V1\Meta\LookupController;
use App\Http\Controllers\api\V1\SchoolSetup\AcademicSectionController;
use App\Http\Controllers\api\V1\SchoolSetup\BatchController;
use App\Http\Controllers\api\V1\SchoolSetup\DivisionController;
use App\Http\Controllers\api\V1\SchoolSetup\DivisionPlannerController;
use App\Http\Controllers\api\V1\SchoolSetup\PeriodController;
use App\Http\Controllers\api\V1\SchoolSetup\StandardController;
use App\Http\Controllers\api\V1\SchoolSetup\StdDivMapController;
use App\Http\Controllers\api\V1\SchoolSetup\SubStdMapController;
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

        // dropdown sources (login only, institute scoped): replace the Blade AJAX helpers
        Route::prefix('lookups')->group(function () {
            Route::get('standards', [LookupController::class, 'standards']);
            Route::get('divisions', [LookupController::class, 'divisions']);
            Route::get('subjects', [LookupController::class, 'subjects']);
            Route::get('academic-sections', [LookupController::class, 'academicSections']);
            Route::get('academic-years', [LookupController::class, 'academicYears']);
            Route::get('content-categories', [LookupController::class, 'contentCategories']);
        });

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

            // Semester Division Mapping (menu std_div_map.index): save = Blade store, which needs the add right
            Route::get('std-div-map', [StdDivMapController::class, 'show'])->middleware(ApiPermission::class.':std_div_map.index,view');
            Route::post('std-div-map', [StdDivMapController::class, 'save'])->middleware(ApiPermission::class.':std_div_map.index,add');

            // Subject Semester Mapping (menu sub_std_map.index). Update is a POST with _method=PUT because it carries a file.
            Route::prefix('sub-std-maps')->group(function () {
                $m = 'sub_std_map.index';
                Route::get('/', [SubStdMapController::class, 'index'])->middleware(ApiPermission::class.":$m,view");
                Route::get('{id}', [SubStdMapController::class, 'show'])->whereNumber('id')->middleware(ApiPermission::class.":$m,view");
                Route::post('/', [SubStdMapController::class, 'store'])->middleware(ApiPermission::class.":$m,add");
                Route::put('{id}', [SubStdMapController::class, 'update'])->whereNumber('id')->middleware(ApiPermission::class.":$m,edit");
                Route::delete('{id}', [SubStdMapController::class, 'destroy'])->whereNumber('id')->middleware(ApiPermission::class.":$m,delete");
            });

            // Create Batch (menu batch_master.index): batches are managed per standard + division group
            Route::prefix('batches')->group(function () {
                $m = 'batch_master.index';
                Route::get('/', [BatchController::class, 'index'])->middleware(ApiPermission::class.":$m,view");
                Route::get('group', [BatchController::class, 'group'])->middleware(ApiPermission::class.":$m,view");
                Route::post('/', [BatchController::class, 'store'])->middleware(ApiPermission::class.":$m,add");
                Route::put('group', [BatchController::class, 'updateGroup'])->middleware(ApiPermission::class.":$m,edit");
                Route::delete('group', [BatchController::class, 'destroyGroup'])->middleware(ApiPermission::class.":$m,delete");
                Route::delete('{id}', [BatchController::class, 'destroy'])->whereNumber('id')->middleware(ApiPermission::class.":$m,delete");
            });

            // Division Planner (menu division_capacity_master.index)
            Route::prefix('division-planner')->group(function () {
                $m = 'division_capacity_master.index';
                Route::get('/', [DivisionPlannerController::class, 'index'])->middleware(ApiPermission::class.":$m,view");
                Route::post('/', [DivisionPlannerController::class, 'store'])->middleware(ApiPermission::class.":$m,add");
                Route::put('{id}', [DivisionPlannerController::class, 'update'])->whereNumber('id')->middleware(ApiPermission::class.":$m,edit");
                Route::delete('{id}', [DivisionPlannerController::class, 'destroy'])->whereNumber('id')->middleware(ApiPermission::class.":$m,delete");
            });

            // Create Periods (menu period_master.index)
            Route::prefix('periods')->group(function () {
                $m = 'period_master.index';
                Route::get('/', [PeriodController::class, 'index'])->middleware(ApiPermission::class.":$m,view");
                Route::get('{id}', [PeriodController::class, 'show'])->whereNumber('id')->middleware(ApiPermission::class.":$m,view");
                Route::post('/', [PeriodController::class, 'store'])->middleware(ApiPermission::class.":$m,add");
                Route::put('{id}', [PeriodController::class, 'update'])->whereNumber('id')->middleware(ApiPermission::class.":$m,edit");
                Route::delete('{id}', [PeriodController::class, 'destroy'])->whereNumber('id')->middleware(ApiPermission::class.":$m,delete");
            });
        });
    });
});
