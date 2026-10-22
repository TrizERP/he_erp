<?php

use App\Http\Controllers\pbas\PbasEntryController;
use App\Http\Controllers\pbas\PbasHodController;
use App\Http\Controllers\pbas\PbasPrincipalController;
use App\Http\Controllers\pbas\PbasProfileController;
use App\Http\Controllers\pbas\PbasReportController;
use App\Services\Pbas\PbasEntries;
use Illuminate\Support\Facades\Route;

// PBAS (Performance Based Appraisal System) routes, registered in RouteServiceProvider like routes/placement.php.
//
// Layers: session (logged in) -> menu (builds the sidebar from tblmenumaster rights) -> logRoute (access log)
// -> check_permissions (view/add/delete rights of the menu row) -> pbas.role (route-level role check).
// Roles come from the session profile name: faculty (any staff) / hod / principal; ERP admins always pass.
// Every menu row in tblmenumaster links to a pbas.*.index name (see PbasMenuSeeder).

$faculty = 'pbas.role:faculty';
$hod = 'pbas.role:hod,principal';
$principal = 'pbas.role:principal';

Route::group(['prefix' => 'pbas', 'middleware' => ['session', 'menu', 'logRoute', 'check_permissions']], function () use ($faculty, $hod, $principal) {

    // Part A: general information (also the PBAS dashboard)
    Route::get('profile', [PbasProfileController::class, 'index'])->name('pbas.profile.index')->middleware($faculty);
    Route::post('profile', [PbasProfileController::class, 'store'])->name('pbas.profile.store')->middleware($faculty);

    // Data entry screens: one set of routes per entry definition
    foreach (array_keys(PbasEntries::all()) as $slug) {
        Route::get("entry/{$slug}", [PbasEntryController::class, 'index'])->defaults('slug', $slug)->name("pbas.{$slug}.index")->middleware($faculty);
        Route::post("entry/{$slug}", [PbasEntryController::class, 'store'])->defaults('slug', $slug)->name("pbas.{$slug}.store")->middleware($faculty);
        Route::delete("entry/{$slug}", [PbasEntryController::class, 'destroy'])->defaults('slug', $slug)->name("pbas.{$slug}.destroy")->middleware($faculty);
    }

    // Your PBAS Summary > API Form
    Route::get('api-form', [PbasReportController::class, 'index'])->name('pbas.report.index')->middleware($faculty);
    Route::get('api-form/{user}', [PbasReportController::class, 'show'])->name('pbas.report.show')->whereNumber('user')->middleware($hod);

    // HOD Activities > Lock/UnLock Data
    Route::get('hod', [PbasHodController::class, 'index'])->name('pbas.hod.index')->middleware($hod);
    Route::post('hod/lock', [PbasHodController::class, 'lock'])->name('pbas.hod.lock')->middleware($hod);
    Route::post('hod/unlock', [PbasHodController::class, 'unlock'])->name('pbas.hod.unlock')->middleware($hod);
    Route::get('hod/comment', [PbasHodController::class, 'comment'])->name('pbas.hod.comment')->middleware($hod);
    Route::post('hod/comment', [PbasHodController::class, 'storeComment'])->name('pbas.hod.comment.store')->middleware($hod);

    // Principal > View data and input ACR
    Route::get('principal', [PbasPrincipalController::class, 'index'])->name('pbas.principal.index')->middleware($principal);
    Route::get('principal/acr', [PbasPrincipalController::class, 'acr'])->name('pbas.principal.acr')->middleware($principal);
    Route::post('principal/acr', [PbasPrincipalController::class, 'storeAcr'])->name('pbas.principal.acr.store')->middleware($principal);
});
