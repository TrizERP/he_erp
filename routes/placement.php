<?php

use App\Http\Controllers\placement\PlacementAnnouncementController;
use App\Http\Controllers\placement\PlacementCompanyController;
use App\Http\Controllers\placement\PlacementDashboardController;
use App\Http\Controllers\placement\PlacementDriveController;
use App\Http\Controllers\placement\PlacementVerificationController;
use App\Http\Controllers\placement\StudentPlacementController;
use App\Http\Controllers\placement\PlacementCoordinatorController;
use App\Http\Controllers\placement\PlacementEligibilityTemplateController;
use App\Http\Controllers\placement\PlacementMasterController;
use App\Http\Controllers\placement\PlacementNotificationController;
use App\Http\Controllers\placement\PlacementOfferController;
use App\Http\Controllers\placement\PlacementOutcomeController;
use App\Http\Controllers\placement\PlacementPortalController;
use App\Http\Controllers\placement\PlacementRequirementController;
use App\Http\Controllers\placement\PlacementResumeReviewController;
use App\Http\Controllers\placement\PlacementTrainingController;
use App\Http\Controllers\placement\PlacementReportController;
use App\Http\Controllers\placement\PlacementRoundController;
use App\Http\Controllers\placement\PlacementSettingController;
use App\Http\Controllers\placement\PlacementPolicyController;
use App\Http\Controllers\placement\PlacementSeasonController;
use App\Http\Controllers\placement\PlacementTemplateController;
use Illuminate\Support\Facades\Route;

// Placement module routes (registered in RouteServiceProvider like routes/internship.php).
//
// Layers: session (logged in) -> menu (builds the menu from tblmenumaster rights) ->
// logRoute (access log) -> placement.role (route-level authorization; ERP admins always pass).
// Roles: tpo = full CRUD, principal = read-all + policy approval, coordinator = read (department
// scoping is applied in later phases).
// Route names are placement.* and each menu row in tblmenumaster links to the .index name.

$read = 'placement.role:tpo,principal,coordinator';
$write = 'placement.role:tpo';
$act = 'placement.role:tpo,coordinator'; // verification and shortlisting (coordinators: own courses only)
$student = 'placement.role:student';

Route::group(['middleware' => ['session', 'menu', 'logRoute']], function () use ($read, $write, $act, $student) {

    // Reusable list + modal CRUD group: /placement/{path}, placement.{name}.*
    $crud = function (string $path, string $name, string $controller) use ($read, $write) {
        Route::prefix("placement/{$path}")->controller($controller)->group(function () use ($name, $read, $write) {
            Route::get('/', 'index')->name("placement.{$name}.index")->middleware($read);
            Route::get('data', 'data')->name("placement.{$name}.data")->middleware($read);
            Route::post('/', 'store')->name("placement.{$name}.store")->middleware($write);
            Route::get('{id}/edit', 'edit')->name("placement.{$name}.edit")->middleware($write)->whereNumber('id');
            Route::put('{id}', 'update')->name("placement.{$name}.update")->middleware($write)->whereNumber('id');
            Route::delete('{id}', 'destroy')->name("placement.{$name}.destroy")->middleware($write)->whereNumber('id');
        });
    };

    // A. Masters / setup
    $crud('masters', 'masters', PlacementMasterController::class);
    $crud('seasons', 'seasons', PlacementSeasonController::class);
    $crud('templates', 'templates', PlacementTemplateController::class);
    $crud('coordinators', 'coordinators', PlacementCoordinatorController::class);

    // B. Policy and eligibility templates
    $crud('policies', 'policies', PlacementPolicyController::class);
    Route::post('placement/policies/{id}/approve', [PlacementPolicyController::class, 'approve'])
        ->name('placement.policies.approve')->middleware('placement.role:principal')->whereNumber('id');
    $crud('eligibility-templates', 'eligibility', PlacementEligibilityTemplateController::class);

    // C. Company management
    Route::prefix('placement/companies')->controller(PlacementCompanyController::class)->group(function () use ($read, $write) {
        Route::get('/', 'index')->name('placement.companies.index')->middleware($read);
        Route::get('data', 'data')->name('placement.companies.data')->middleware($read);
        Route::get('create', 'create')->name('placement.companies.create')->middleware($write);
        Route::post('/', 'store')->name('placement.companies.store')->middleware($write);
        Route::get('{company}', 'show')->name('placement.companies.show')->middleware($read)->whereNumber('company');
        Route::get('{company}/edit', 'edit')->name('placement.companies.edit')->middleware($write)->whereNumber('company');
        Route::put('{company}', 'update')->name('placement.companies.update')->middleware($write)->whereNumber('company');
        Route::delete('{company}', 'destroy')->name('placement.companies.destroy')->middleware($write)->whereNumber('company');

        Route::post('{company}/status', 'transition')->name('placement.companies.status')->middleware($write)->whereNumber('company');
        Route::post('{company}/feedback', 'feedback')->name('placement.companies.feedback')->middleware($write)->whereNumber('company');
        Route::post('{company}/contacts', 'storeContact')->name('placement.companies.contacts.store')->middleware($write)->whereNumber('company');
        Route::delete('{company}/contacts/{contact}', 'destroyContact')->name('placement.companies.contacts.destroy')->middleware($write)->whereNumber(['company', 'contact']);
        Route::post('{company}/communications', 'storeCommunication')->name('placement.companies.communications.store')->middleware($write)->whereNumber('company');
        Route::post('{company}/communications/{communication}/done', 'completeFollowUp')->name('placement.companies.followups.done')->middleware($write)->whereNumber(['company', 'communication']);
    });

    // D. Job drives
    Route::prefix('placement/drives')->controller(PlacementDriveController::class)->group(function () use ($read, $write, $act) {
        Route::get('/', 'index')->name('placement.drives.index')->middleware($read);
        Route::get('data', 'data')->name('placement.drives.data')->middleware($read);
        Route::get('create', 'create')->name('placement.drives.create')->middleware($write);
        Route::post('/', 'store')->name('placement.drives.store')->middleware($write);
        Route::get('{drive}', 'show')->name('placement.drives.show')->middleware($read)->whereNumber('drive');
        Route::get('{drive}/edit', 'edit')->name('placement.drives.edit')->middleware($write)->whereNumber('drive');
        Route::put('{drive}', 'update')->name('placement.drives.update')->middleware($write)->whereNumber('drive');
        Route::delete('{drive}', 'destroy')->name('placement.drives.destroy')->middleware($write)->whereNumber('drive');
        Route::post('{drive}/status', 'transition')->name('placement.drives.status')->middleware($write)->whereNumber('drive');

        Route::post('{drive}/roles', 'storeRole')->name('placement.drives.roles.store')->middleware($write)->whereNumber('drive');
        Route::delete('{drive}/roles/{role}', 'destroyRole')->name('placement.drives.roles.destroy')->middleware($write)->whereNumber(['drive', 'role']);
        Route::post('{drive}/eligibility', 'saveEligibility')->name('placement.drives.eligibility.save')->middleware($write)->whereNumber('drive');
        Route::delete('{drive}/eligibility/{eligibility}', 'destroyEligibility')->name('placement.drives.eligibility.destroy')->middleware($write)->whereNumber(['drive', 'eligibility']);
        Route::post('{drive}/recompute', 'recompute')->name('placement.drives.recompute')->middleware($write)->whereNumber('drive');

        Route::get('{drive}/eligible-data', 'eligibleData')->name('placement.drives.eligible.data')->middleware($read)->whereNumber('drive');
        Route::get('{drive}/applicants-data', 'applicantsData')->name('placement.drives.applicants.data')->middleware($read)->whereNumber('drive');
        Route::get('{drive}/applicants-export', 'applicantsExport')->name('placement.drives.applicants.export')->middleware($read)->whereNumber('drive');
        Route::post('{drive}/applicants/status', 'bulkStatus')->name('placement.drives.applicants.status')->middleware($act)->whereNumber('drive');
        Route::post('{drive}/applicants/override', 'overrideApply')->name('placement.drives.applicants.override')->middleware($write)->whereNumber('drive');
    });

    // E. Profile verification (TPO / coordinator)
    Route::prefix('placement/verification')->controller(PlacementVerificationController::class)->group(function () use ($read, $act) {
        Route::get('/', 'index')->name('placement.verification.index')->middleware($read);
        Route::get('data', 'data')->name('placement.verification.data')->middleware($read);
        Route::get('{profile}', 'show')->name('placement.verification.show')->middleware($read)->whereNumber('profile');
        Route::post('{profile}', 'decide')->name('placement.verification.decide')->middleware($act)->whereNumber('profile');
    });

    // F. Rounds, attendance, results, interview slots
    Route::controller(PlacementRoundController::class)->group(function () use ($read, $write, $act) {
        Route::get('placement/drives/{drive}/rounds', 'index')->name('placement.rounds.index')->middleware($read)->whereNumber('drive');
        Route::post('placement/drives/{drive}/rounds', 'store')->name('placement.rounds.store')->middleware($write)->whereNumber('drive');
        Route::prefix('placement/rounds')->group(function () use ($read, $write, $act) {
            Route::get('{round}', 'show')->name('placement.rounds.show')->middleware($read)->whereNumber('round');
            Route::post('{round}/seed', 'seed')->name('placement.rounds.seed')->middleware($write)->whereNumber('round');
            Route::post('{round}/save', 'save')->name('placement.rounds.save')->middleware($act)->whereNumber('round');
            Route::post('{round}/publish', 'publish')->name('placement.rounds.publish')->middleware($write)->whereNumber('round');
            Route::post('{round}/notify', 'notifySchedule')->name('placement.rounds.notify')->middleware($write)->whereNumber('round');
            Route::post('{round}/slots', 'storeSlot')->name('placement.rounds.slots.store')->middleware($write)->whereNumber('round');
            Route::post('{round}/slots/auto', 'autoSlots')->name('placement.rounds.slots.auto')->middleware($write)->whereNumber('round');
            Route::delete('{round}/slots/{slot}', 'destroySlot')->name('placement.rounds.slots.destroy')->middleware($write)->whereNumber(['round', 'slot']);
            Route::delete('{round}', 'destroy')->name('placement.rounds.destroy')->middleware($write)->whereNumber('round');
        });
    });

    // G. Offers
    Route::prefix('placement/offers')->controller(PlacementOfferController::class)->group(function () use ($read, $write) {
        Route::get('/', 'index')->name('placement.offers.index')->middleware($read);
        Route::get('data', 'data')->name('placement.offers.data')->middleware($read);
        Route::get('create', 'create')->name('placement.offers.create')->middleware($write);
        Route::post('/', 'store')->name('placement.offers.store')->middleware($write);
        Route::get('{offer}', 'show')->name('placement.offers.show')->middleware($read)->whereNumber('offer');
        Route::post('{offer}/status', 'transition')->name('placement.offers.status')->middleware($write)->whereNumber('offer');
        Route::post('{offer}/verify', 'verify')->name('placement.offers.verify')->middleware($write)->whereNumber('offer');
    });

    // I. Communication: announcements, bulk send, log
    $crud('announcements', 'announcements', PlacementAnnouncementController::class);
    Route::prefix('placement/notifications')->controller(PlacementNotificationController::class)->group(function () use ($write) {
        Route::get('/', 'index')->name('placement.notifications.index')->middleware('placement.role:tpo,principal');
        Route::get('data', 'data')->name('placement.notifications.data')->middleware('placement.role:tpo,principal');
        Route::post('send', 'send')->name('placement.notifications.send')->middleware($write);
        Route::post('{id}/resend', 'resend')->name('placement.notifications.resend')->middleware($write)->whereNumber('id');
    });

    // L. Settings and audit log
    Route::prefix('placement/settings')->controller(PlacementSettingController::class)->group(function () use ($write) {
        Route::get('/', 'index')->name('placement.settings.index')->middleware('placement.role:tpo,principal');
        Route::post('/', 'update')->name('placement.settings.update')->middleware($write);
        Route::get('audit-data', 'auditData')->name('placement.settings.audit')->middleware('placement.role:tpo,principal');
    });

    // Dashboards (one route; the view follows the role) and reports
    Route::get('placement/dashboard', [PlacementDashboardController::class, 'index'])
        ->name('placement.dashboard')->middleware('placement.role:tpo,principal,coordinator,student');

    Route::prefix('placement/reports')->controller(PlacementReportController::class)->middleware($read)->group(function () {
        Route::get('/', 'index')->name('placement.reports.index');
        Route::get('{key}', 'show')->name('placement.reports.show')->where('key', '[a-z_]+');
        Route::get('{key}/data', 'data')->name('placement.reports.data')->where('key', '[a-z_]+');
        Route::get('{key}/export/{format}', 'export')->name('placement.reports.export')->where('key', '[a-z_]+')->where('format', 'excel|pdf');
    });

    // H. Training, mock tests / interviews, resume reviews
    Route::prefix('placement/training')->controller(PlacementTrainingController::class)->group(function () use ($read, $write, $act) {
        Route::get('/', 'index')->name('placement.training.index')->middleware($read);
        Route::get('data', 'data')->name('placement.training.data')->middleware($read);
        Route::get('create', 'create')->name('placement.training.create')->middleware($write);
        Route::post('/', 'store')->name('placement.training.store')->middleware($write);
        Route::get('{training}', 'show')->name('placement.training.show')->middleware($read)->whereNumber('training');
        Route::get('{training}/edit', 'edit')->name('placement.training.edit')->middleware($write)->whereNumber('training');
        Route::put('{training}', 'update')->name('placement.training.update')->middleware($write)->whereNumber('training');
        Route::delete('{training}', 'destroy')->name('placement.training.destroy')->middleware($write)->whereNumber('training');
        Route::post('{training}/assign', 'assign')->name('placement.training.assign')->middleware($write)->whereNumber('training');
        Route::post('{training}/save', 'save')->name('placement.training.save')->middleware($act)->whereNumber('training');
    });
    Route::prefix('placement/resume-reviews')->controller(PlacementResumeReviewController::class)->group(function () use ($read, $act) {
        Route::get('/', 'index')->name('placement.resume_reviews.index')->middleware($read);
        Route::get('data', 'data')->name('placement.resume_reviews.data')->middleware($read);
        Route::get('{review}', 'show')->name('placement.resume_reviews.show')->middleware($read)->whereNumber('review');
        Route::post('{review}', 'respond')->name('placement.resume_reviews.respond')->middleware($act)->whereNumber('review');
    });

    // J. Higher studies / entrepreneurship / other outcomes
    $crud('outcomes', 'outcomes', PlacementOutcomeController::class);

    // Recruiter portal: requirements review and link generation (the portal itself is below, outside the login)
    Route::prefix('placement/requirements')->controller(PlacementRequirementController::class)->group(function () use ($read, $write) {
        Route::get('/', 'index')->name('placement.requirements.index')->middleware($read);
        Route::get('data', 'data')->name('placement.requirements.data')->middleware($read);
        Route::post('{requirement}', 'decide')->name('placement.requirements.decide')->middleware($write)->whereNumber('requirement');
    });
    Route::post('placement/drives/{drive}/portal-link', [PlacementPortalController::class, 'driveLink'])
        ->name('placement.drives.portal_link')->middleware($write)->whereNumber('drive');
    Route::post('placement/companies/{company}/portal-link', [PlacementPortalController::class, 'companyLink'])
        ->name('placement.companies.portal_link')->middleware($write)->whereNumber('company');

    // Student-facing screens
    Route::prefix('placement/student')->controller(StudentPlacementController::class)->middleware($student)->group(function () {
        Route::get('profile', 'profile')->name('placement.student.profile');
        Route::put('profile', 'updateProfile')->name('placement.student.profile.update');
        Route::post('register', 'register')->name('placement.student.register');
        Route::post('opt-out', 'optOut')->name('placement.student.optout');
        Route::get('resume', 'resume')->name('placement.student.resume');
        Route::get('drives', 'drives')->name('placement.student.drives');
        Route::post('drives/{drive}/apply', 'apply')->name('placement.student.apply')->whereNumber('drive');
        Route::get('applications', 'applications')->name('placement.student.applications');
        Route::get('rounds', 'rounds')->name('placement.student.rounds');
        Route::get('attend/{round}/{token}', 'attend')->name('placement.student.attend')->whereNumber('round');
        Route::get('offers', 'offers')->name('placement.student.offers');
        Route::post('offers/{offer}/respond', 'respondOffer')->name('placement.student.offers.respond')->whereNumber('offer');
        Route::get('notices', 'notices')->name('placement.student.notices');
        Route::get('training', 'training')->name('placement.student.training');
        Route::post('resume-review', 'requestReview')->name('placement.student.review');
        Route::post('applications/{application}/withdraw', 'withdraw')->name('placement.student.withdraw')->whereNumber('application');
    });
});

// Recruiter portal: no ERP login. Access is a time-limited signed link generated by the TPO.
Route::prefix('placement/portal')->controller(PlacementPortalController::class)->middleware(['signed', 'throttle:30,1'])->group(function () {
    Route::get('drive/{drive}', 'drive')->name('placement.portal.drive')->whereNumber('drive');
    Route::post('drive/{drive}/results', 'results')->name('placement.portal.results')->whereNumber('drive');
    Route::get('company/{company}', 'company')->name('placement.portal.company')->whereNumber('company');
    Route::post('company/{company}/requirement', 'requirement')->name('placement.portal.requirement')->whereNumber('company');
});
