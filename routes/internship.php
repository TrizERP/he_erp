
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\internship\InternshipController;
use App\Http\Controllers\internship\CompanyController;
use App\Http\Controllers\internship\InternshipStudentController;
use App\Http\Controllers\internship\InternshipMarkController;
use App\Http\Controllers\internship\InternshipAttendanceController;
use App\Http\Controllers\internship\InternshipShiftController;
use App\Http\Controllers\internship\InternshipHolidayController;
use App\Http\Controllers\internship\StudentInternshipController;
// This file defines the routes for the internship module in a Laravel application.
// It includes routes for managing internships, companies, students, marks, attendance, shifts, and holidays.
// The routes are grouped by middleware for session management, menu rendering, and logging of routes.
// The admin routes allow for full CRUD operations on internships, companies, and related entities.
// The student routes allow students to view their internships and mark attendance or checkout.
// The routes are organized to ensure proper access control and functionality for both admin and student users. 
// The code is structured to follow RESTful conventions, using resource controllers for managing entities.

// Admin routes
Route::group([ 'middleware' => ['session', 'menu', 'logRoute']], function () {
    // Internships
    Route::resource('internships', InternshipController::class);
    
    // Companies
    Route::resource('companies', CompanyController::class);
    
    // Internship Students
    Route::prefix('internships/{internship}')->group(function () {
        Route::resource('students', InternshipStudentController::class)->except(['show']);
        
        // Internship Marks
        Route::prefix('students/{student}')->group(function () {
            Route::resource('marks', InternshipMarkController::class)->except(['index', 'show']);
        });
        Route::get('students/{student}/marks', [InternshipMarkController::class, 'index'])
             ->name('internships.students.marks.index');
        
        // Internship Attendance
        Route::prefix('students/{student}')->group(function () {
            Route::resource('attendance', InternshipAttendanceController::class)->except(['index', 'show']);
        });
        Route::get('students/{student}/attendance', [InternshipAttendanceController::class, 'index'])
             ->name('internships.students.attendance.index');
        
        // Internship Shifts
        Route::resource('shifts', InternshipShiftController::class)->except(['show']);
        
        // Internship Holidays
        Route::resource('holidays', InternshipHolidayController::class)->except(['show']);
    });
});

// Student routes
Route::group([ 'middleware' => ['session', 'menu', 'logRoute']], function () {
    Route::prefix('student')->group(function () {
        Route::get('internships', [StudentInternshipController::class, 'index'])->name('student.internships.index');
        Route::get('internships/{internship}', [StudentInternshipController::class, 'show'])->name('student.internships.show');
        Route::post('internships/{internship}/attendance', [StudentInternshipController::class, 'markAttendance'])->name('student.internships.attendance');
        Route::post('internships/{internship}/checkout', [StudentInternshipController::class, 'markCheckOut'])->name('student.internships.checkout');
    });
});