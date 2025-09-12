<?php

use App\Http\Controllers\user\mobileapp_menu_rightsController;
use App\Http\Controllers\user\tblgroupwise_rightsController;
use App\Http\Controllers\user\tblindividual_rightsController;
use App\Http\Controllers\user\tbluserController;
use App\Http\Controllers\user\tbluserPastEducationController;
use App\Http\Controllers\user\tbluserprofilemasterController;
use App\Http\Controllers\user\tbluserProfileWiseMenuController;
use App\Http\Controllers\user\userReportController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'user', 'middleware' => ['session', 'menu', 'logRoute']], function () {
    Route::resource('add_user_profile', tbluserprofilemasterController::class);
    Route::resource('add_user', tbluserController::class);
    Route::resource('add_groupwise_rights', tblgroupwise_rightsController::class);
    Route::resource('add_mobileapp_menu_rights', mobileapp_menu_rightsController::class);
    Route::resource('add_user_past_education', tbluserPastEducationController::class);
    Route::resource('user_profile_wise_menu_rights', tbluserProfileWiseMenuController::class);
    Route::resource('user_report', userReportController::Class);
    Route::post('show_user_report', [userReportController::class, 'searchUser'])->name("show_user_report");
     Route::post('ajax_userProfile_Data_Create',
        [tblgroupwise_rightsController::class, 'ajax_userProfile_Data_Create'])->name('ajax_userProfile_Data_Create');
    Route::get('ajax_groupwiserights',
        [tblgroupwise_rightsController::class, 'displayGroupwiseRights'])->name('ajax_groupwiserights');
    Route::get('ajax_pasteducation',
        [tbluserPastEducationController::class, 'addUpdateUserPastEducation'])->name('ajax_pasteducation');
    Route::resource('add_individual_rights', tblindividual_rightsController::class);
    Route::get('ajax_profileWiseUsers',
        [tblindividual_rightsController::Class, 'profileWiseUsers'])->name('ajax_profileWiseUsers');
    Route::get('ajax_individualrights',
        [tblindividual_rightsController::class, 'displayIndividualRights'])->name('ajax_individualrights');
    Route::get('ajax_user_profile_wise_rights',
        [tbluserProfileWiseMenuController::class, 'displayUserProfileWiseRights'])->name('ajax_user_profile_wise_rights');

    Route::post('add_user/{id}/edit', [tbluserController::class,'storePastEducation'])->name('edi_tbl_user.store');

    Route::get('delete_data/{id}', [tbluserController::class,'deleteData'])->name('deleteData.destroy');
});




