<?php


use App\Http\Controllers\implementation\implementation_MasterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\time_table\createTimetableController;
use App\Http\Controllers\time_table\timetableReportsController;

Route::group(['prefix' => 'implementation', 'middleware' => ['session', 'menu', 'logRoute','check_permissions']], function () {
    Route::resource('add_implementation', implementation_MasterController::class);
});

Route::group(['prefix' => 'timetable', 'middleware' => ['session', 'menu', 'logRoute','check_permissions']], function () {
    // create timetabel 
    Route::resource('create-timetable', createTimetableController::class);
    Route::get('add_remove_Batch_Timetable', [createTimetableController::class, 'getBatchTimetable'])->name('add_remove_Batch_Timetable');
    Route::get('Delete_Timetable', [createTimetableController::class, 'deleteTimetable'])->name('Delete_Timetable');

    // classwise report timetable 
    Route::resource('classwise_timetable', timetableReportsController::class);
    Route::get('facultywise_timetable', [timetableReportsController::class,'facultyTimetableIndex'])->name('facultywise_timetable.index');
    Route::get('facultywise_timetable/create', [timetableReportsController::class,'facultyTimetableCreate'])->name('facultywise_timetable.create');

});