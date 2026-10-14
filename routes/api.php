<?php

use App\Http\Controllers\api\apiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('whats-send-app',function (Request $request) {
    \Illuminate\Support\Facades\Log::info(json_encode($request->all()));
});

Route::post('whats-comming-app',function (Request $request) {
    \Illuminate\Support\Facades\Log::info(json_encode($request->all()));
});

Route::post('update-message',[\App\Http\Controllers\WhatsappController::class,'updateDeliveryStatus']);
Route::post('incoming-message',[\App\Http\Controllers\WhatsappController::class,'incomingMessage']);

// Placement module: student endpoints for the mobile app (JWT, same as the apiController routes).
Route::controller(\App\Http\Controllers\api\PlacementApiController::class)->prefix('placement')->group(function () {
    Route::post('dashboard', 'dashboard');
    Route::post('profile', 'profile');
    Route::post('profile_update', 'updateProfile');
    Route::post('register', 'register');
    Route::post('opt_out', 'optOut');
    Route::post('drives', 'drives');
    Route::post('apply', 'apply');
    Route::post('applications', 'applications');
    Route::post('withdraw', 'withdraw');
    Route::post('rounds', 'rounds');
    Route::post('offers', 'offers');
    Route::post('offer_respond', 'respondOffer');
    Route::post('notices', 'notices');
    Route::post('training', 'training');
});

Route::controller(apiController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('check_otp', 'check_otp');
    Route::post('homescreen', 'homescreen');
    Route::post('teacherlogin', 'teacherlogin');
    Route::post('teacher_check_otp', 'teacher_check_otp');
    Route::post('playscreen', 'playscreen');
    Route::post('homescreen', 'homescreen');
    Route::post('gcm_insert', 'gcm_insert');
    Route::get('testkey', 'testkey');
});

// 12-11-2024
// Route::get('crm-whatsapp', [\App\Http\Controllers\WhatsappController::class, 'whatsappCRM'])->withoutMiddleware([Authenticate::class])->name('crm-whatsapp');
// Route::get('crm-whatsapp-update', [\App\Http\Controllers\WhatsappController::class, 'updateCRMWhatsappStatus'])->withoutMiddleware([Authenticate::class])->name('updateCRMWhatsappStatus');

// // 27-01-2025 only for API
// Route::get('/compliance/list',[instituteDetailController::class,'index']);
// Route::post('/compliance/create',[instituteDetailController::class,'store']);
// Route::post('/compliance/update/{id}',[instituteDetailController::class,'update']);
// Route::post('/compliance/delete/{id}',[instituteDetailController::class,'destroy']);