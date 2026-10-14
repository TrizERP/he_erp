<?php

use App\Http\Controllers\admission\admissionEnquiryController;
use App\Http\Controllers\admission\admissionFollowUpController;
use App\Http\Controllers\admission\admissionFormController;
use App\Http\Controllers\admission\admissionRegistrationController;
use App\Http\Controllers\admission\admissionReportController;
use App\Http\Controllers\admission\onlineAdmissionConfirmController;
use App\Http\Controllers\admission\admissionStatusController;
use App\Http\Controllers\admission\AdmissionApplicationController;
use App\Http\Controllers\admission\AdmissionConfirmController;
use App\Http\Controllers\admission\AdmissionDashboardController;
use App\Http\Controllers\admission\AdmissionDocumentController;
use App\Http\Controllers\admission\AdmissionFeeController;
use App\Http\Controllers\admission\AdmissionOfferController;
use App\Http\Controllers\admission\AdmissionProcessReportController;
use App\Http\Controllers\admission\AdmissionSettingController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'admission', 'middleware' => ['session', 'menu', 'logRoute','check_permissions']], static function () {
    Route::resource('admission_enquiry', admissionEnquiryController::class);
    Route::resource('admission_registration', admissionFormController::class);
    Route::resource('admission_confirmation', admissionRegistrationController::class);
    Route::resource('admission_follow_up', admissionFollowUpController::class);
    Route::resource('online_admission_confirm', onlineAdmissionConfirmController::class);

    Route::controller(admissionRegistrationController::class)->group(function () {
        Route::post('admission_student', 'saveStudent')->name('admission_student');
    });

    Route::controller(admissionReportController::class)->group(function () {
        Route::any('admission_enquiry_report', 'enquiryReport')->name('admission_enquiry_report');
        Route::any('admission_registration_report', 'formReport')->name('admission_registration_report');
        Route::any('admission_without_con_report', 'regReport')->name('admission_without_con_report');
        Route::any('admission_confirmation_report', 'conReport')->name('admission_confirmation_report');
        Route::any('admission_enquiry_followup_report', 'followUpReport')->name('admission_enquiry_followup_report');
    });

    Route::controller(onlineAdmissionConfirmController::class)->group(function () {
        Route::any('online_admission_report', 'onlineAdmissionReport')->name('online_admission_report');
        Route::post('ajax_AdmissionConfirmReport', 'ajax_AdmissionConfirmReport')->name('ajax_AdmissionConfirmReport');
    });

    Route::controller(admissionEnquiryController::class)->group(function () {
        Route::get('ajax_getFeesBreakoff', 'ajax_getFeesBreakoff')->name('ajax_getFeesBreakoff');
    });

});

Route::controller(admissionEnquiryController::class)->group(function () {
    Route::get('admission/online-admission/{id}/{title}', 'onlineEnquiryFirst')->name('onlineEnquiryFirst');
    Route::get('admission/online-admission-enquiry/{id}/{title}', 'onlineEnquiry')->name('onlineEnquiry');
    Route::post('admission/process-admission-enquiry', 'processOnlineEnquiry')->name('processOnlineEnquiry');
    Route::any('admission/admission-receipt', 'receipt')->name('receipt');

    Route::get('ajax_listCalendarData', 'ajax_listCalendarData')
        ->middleware('logRoute')->name('ajax_listCalendarData');

});

Route::controller(admissionRegistrationController::class)->group(function () {
    Route::get('ajax_getDivision', 'ajax_getDivision')->name('ajax_getDivision');
});

Route::get('admission_enquiry', [admissionEnquiryController::class, 'create']); // for standalone
Route::post('admission_enquiry/store', [admissionEnquiryController::class, 'store'])->name('admission_enquiry.storeNew'); // for standalone

// admission enquiry Details
Route::get('admission_details', [admissionEnquiryController::class, 'admissionData'])->name('admissionDetails.index');

// admission tracking
Route::resource('admission_status', admissionStatusController::class);


/*
 | Admission workflow (review, documents, eligibility, selection, offers, fees, confirmation, reports, settings).
 | Extends the legacy admission module above; it adds workflow state keyed by admission_enquiry.id.
 |
 | Layers: session (logged in) -> menu -> logRoute -> check_permissions (menu rights for menu-linked screens)
 | -> admission.role (route-level capability check; ERP admins always pass). Every record-level action is
 | additionally scoped to the session sub_institute_id inside the services.
 | Menu rows in tblmenumaster link to the *.index route names (see AdmissionMenuSeeder).
 */
Route::group(['prefix' => 'admission/process', 'middleware' => ['session', 'menu', 'logRoute', 'check_permissions']], static function () {
    $view = 'admission.role:view';
    $docs = 'admission.role:manage,documents,decide'; // academic documents: not for fee-only users

    // Dashboard + drill-down
    Route::get('dashboard', [AdmissionDashboardController::class, 'index'])->name('admission.dashboard.index')->middleware($view);
    Route::get('dashboard/{metric}', [AdmissionDashboardController::class, 'drill'])->name('admission.dashboard.drill')->middleware($view);

    // Lists (all backed by the same scoped query)
    Route::get('applications', [AdmissionApplicationController::class, 'index'])->name('admission.review.index')->middleware($view);
    Route::get('merit', [AdmissionApplicationController::class, 'merit'])->name('admission.merit.index')->middleware('admission.role:decide,manage');
    Route::get('confirmation', [AdmissionApplicationController::class, 'confirmList'])->name('admission.confirm.index')->middleware('admission.role:confirm,manage');
    Route::get('admitted', [AdmissionApplicationController::class, 'admitted'])->name('admission.admitted.index')->middleware($view);

    // Application workspace + its actions
    Route::prefix('applications/{enquiry}')->whereNumber('enquiry')->group(function () use ($view) {
        Route::get('/', [AdmissionApplicationController::class, 'show'])->name('admission.review.show')->middleware($view);
        Route::post('convert', [AdmissionApplicationController::class, 'convert'])->name('admission.process.convert')->middleware('admission.role:manage');
        Route::post('submit', [AdmissionApplicationController::class, 'submit'])->name('admission.process.submit')->middleware('admission.role:manage');
        Route::post('reopen', [AdmissionApplicationController::class, 'reopen'])->name('admission.process.reopen')->middleware('admission.role:manage');
        Route::post('inquiry-status', [AdmissionApplicationController::class, 'inquiryStatus'])->name('admission.process.inquiry_status')->middleware('admission.role:manage');
        Route::post('eligibility/evaluate', [AdmissionApplicationController::class, 'evaluateEligibility'])->name('admission.process.eligibility_run')->middleware('admission.role:manage');
        Route::post('eligibility/override', [AdmissionApplicationController::class, 'overrideEligibility'])->name('admission.process.eligibility_override')->middleware('admission.role:override');
        Route::post('selection', [AdmissionApplicationController::class, 'decideSelection'])->name('admission.process.selection')->middleware('admission.role:decide');
        Route::post('documents', [AdmissionDocumentController::class, 'upload'])->name('admission.process.document_upload')->middleware('admission.role:manage');
        Route::post('offer', [AdmissionOfferController::class, 'issue'])->name('admission.process.offer_issue')->middleware('admission.role:offer');
        Route::get('receipt/{kind}', [AdmissionFeeController::class, 'receipt'])->name('admission.process.receipt')->middleware('admission.role:fees,manage');
        Route::post('fee/waive', [AdmissionFeeController::class, 'waive'])->name('admission.process.fee_waive')->middleware('admission.role:waive');
        Route::post('confirm', [AdmissionConfirmController::class, 'confirm'])->name('admission.process.confirm')->middleware('admission.role:confirm');
        Route::post('confirmation-status', [AdmissionConfirmController::class, 'status'])->name('admission.process.confirmation_status')->middleware('admission.role:confirm');
    });

    // Documents
    Route::get('documents', [AdmissionDocumentController::class, 'index'])->name('admission.documents.index')->middleware($docs);
    Route::get('documents/{document}/download', [AdmissionDocumentController::class, 'download'])->name('admission.process.document_download')->middleware($docs)->whereNumber('document');
    Route::post('documents/{document}/verify', [AdmissionDocumentController::class, 'verify'])->name('admission.process.document_verify')->middleware('admission.role:documents')->whereNumber('document');
    Route::post('documents/{document}/reject', [AdmissionDocumentController::class, 'reject'])->name('admission.process.document_reject')->middleware('admission.role:documents')->whereNumber('document');

    // Offers
    Route::get('offers', [AdmissionOfferController::class, 'index'])->name('admission.offers.index')->middleware($view);
    Route::post('offers/{offer}/respond', [AdmissionOfferController::class, 'respond'])->name('admission.process.offer_respond')->middleware('admission.role:offer')->whereNumber('offer');
    Route::post('offers/{offer}/withdraw', [AdmissionOfferController::class, 'withdraw'])->name('admission.process.offer_withdraw')->middleware('admission.role:offer')->whereNumber('offer');

    // Fees
    Route::get('fees', [AdmissionFeeController::class, 'index'])->name('admission.fees.index')->middleware('admission.role:fees');

    // Reports
    Route::get('reports', [AdmissionProcessReportController::class, 'index'])->name('admission.reports.index')->middleware($view);
    Route::get('reports/{type}/export', [AdmissionProcessReportController::class, 'export'])->name('admission.process.report_export')->middleware($view);

    // Settings (admin only)
    Route::get('settings', [AdmissionSettingController::class, 'index'])->name('admission.settings.index')->middleware('admission.role:settings');
    Route::post('settings', [AdmissionSettingController::class, 'save'])->name('admission.process.settings_save')->middleware('admission.role:settings');
    Route::post('settings/requirements', [AdmissionSettingController::class, 'storeRequirement'])->name('admission.process.requirement_add')->middleware('admission.role:settings');
    Route::put('settings/requirements/{requirement}', [AdmissionSettingController::class, 'updateRequirement'])->name('admission.process.requirement_edit')->middleware('admission.role:settings')->whereNumber('requirement');
    Route::delete('settings/requirements/{requirement}', [AdmissionSettingController::class, 'destroyRequirement'])->name('admission.process.requirement_remove')->middleware('admission.role:settings')->whereNumber('requirement');
    Route::post('settings/rules', [AdmissionSettingController::class, 'storeRule'])->name('admission.process.rule_add')->middleware('admission.role:settings');
    Route::delete('settings/rules/{rule}', [AdmissionSettingController::class, 'destroyRule'])->name('admission.process.rule_remove')->middleware('admission.role:settings')->whereNumber('rule');
});
