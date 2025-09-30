<?php

use App\Http\Controllers\AJAXController;
use App\Http\Controllers\api\NewLMS_ApiController;
use App\Http\Controllers\api\NewLMS_StudentApiController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\CkeditorFileUploadController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\easy_com\send_birthday_notification\send_birthday_notification_controller;
use App\Http\Controllers\loginController;
use App\Http\Controllers\school_setup\batchController;
use App\Http\Controllers\school_setup\changePasswordController;
use App\Http\Controllers\school_setup\chapterController;
use App\Http\Controllers\school_setup\classteacherController;
use App\Http\Controllers\school_setup\classteacherReportController;
use App\Http\Controllers\school_setup\classwisetimetableController;
use App\Http\Controllers\school_setup\dashboardSettingController;
use App\Http\Controllers\school_setup\divisionCapacityMasterController;
use App\Http\Controllers\school_setup\erpstatusController;
use App\Http\Controllers\school_setup\facultywisetimetableController;
use App\Http\Controllers\school_setup\lessonplanningController;
use App\Http\Controllers\school_setup\lessonplanningReportController;
use App\Http\Controllers\school_setup\periodController;
use App\Http\Controllers\school_setup\proxyController;
use App\Http\Controllers\school_setup\proxyReportController;
use App\Http\Controllers\school_setup\schoolController;
use App\Http\Controllers\school_setup\std_divController;
use App\Http\Controllers\school_setup\sub_std_mapController;
use App\Http\Controllers\school_setup\subject1Controller;
use App\Http\Controllers\school_setup\teacherdailyReportController;
use App\Http\Controllers\school_setup\teachertransferController;
use App\Http\Controllers\school_setup\timetableController;
use App\Http\Controllers\school_setup\todaysproxyReportController;
use App\Http\Controllers\school_setup\topicController;
use App\Http\Controllers\school_setup\workflowController;
use App\Http\Controllers\signupController;
use App\Http\Controllers\institute_detail;
use App\Http\Controllers\normClatureController;
use App\Http\Controllers\lms\questionWiseReportController;
use App\Http\Controllers\template_result\TemplateResult;
use App\Http\Controllers\tourController;
use App\Http\Controllers\used_storage_graphController;
use App\Http\Controllers\UserFormbuilderController;
use App\Models\general_dataModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Import\ImportController;
use App\Http\Controllers\Import\questionExcelDownloadController;
use App\Http\Controllers\leave\ApplyLeaveController;
use App\Http\Controllers\leave\HolidayController;
use App\Http\Controllers\leave\LeaveController;
use App\Http\Controllers\leave\LeaveTypeController;
use App\Http\Controllers\Payroll\PayrollController;
use App\Http\Controllers\HRMS\HrmsController;
use App\Http\Controllers\library\BookController;
use App\Http\Controllers\library\LibraryReportController;
use App\Http\Controllers\sqaa\sqaa_controller;
use App\Http\Controllers\sqaa\sqaaReportController;
use App\Http\Controllers\sqaa\sqaaScoreReportController;
use App\Http\Controllers\sqaa\sqaaPartsController;
use App\Http\Controllers\attendance\attendanceController;
use App\Http\Controllers\attendance\attendanceReportController;
use App\Http\Controllers\oldDocumentTransfer;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\attendance\monthwiseAttendanceReportController;
use App\Http\Controllers\easy_com\send_sms_parents\send_sms_parents_controller;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

if (isset($_REQUEST['sub_institute_id']) && $_REQUEST['sub_institute_id'] != '') {
    $sub_institute_id = $_REQUEST['sub_institute_id'];

    $get_general_data = general_dataModel::where(['sub_institute_id' => $sub_institute_id])->get()->toArray();

    if (count($get_general_data) > 0) {
        $loginpage_link = $get_general_data[0]['fieldvalue'];
        $loginpage_logo = $get_general_data[1]['fieldvalue'];
        $loginpage_title = $get_general_data[2]['fieldvalue'];
        $loginpage_description = $get_general_data[3]['fieldvalue'];
        $loginpage_favicon = $get_general_data[4]['fieldvalue'];
        $loginpage_backgrond = $get_general_data[5]['fieldvalue'];

        session(['loginpage_link' => $loginpage_link]);
        session(['loginpage_logo' => $loginpage_logo]);
        session(['loginpage_title' => $loginpage_title]);
        session(['loginpage_description' => $loginpage_description]);
        session(['loginpage_favicon' => $loginpage_favicon]);
        session(['loginpage_backgrond' => $loginpage_backgrond]);
    }
} else {
    Route::get('/', function (Request $request) {
        $user_id = $request->session()->get('user_id');
        $expire_date = $request->session()->get('expire_date');

        if (!empty($user_id)) {
            if ($expire_date == null) {
                return redirect()->route('dashboard');
            } else {
                return redirect()->route('setup-institute-details');
            }
        } else {
            return view('login');
        }
    })->name('home');
    // echo 'aaaa';die;
}

//PAYROLL SYSTEM
Route::group(['middleware' => ['session', 'menu', 'logRoute','check_permissions']], function () {
    Route::get('/payroll-type', [PayrollController::class, 'payrollType'])->name('payroll_type.index');
    Route::get('/payroll-type/create', [PayrollController::class, 'payrollCreate'])->name('payroll_type.create');
    Route::post('/payroll-type/store', [PayrollController::class, 'payrollStore'])->name('payroll_type.store');
    Route::get('/payroll-type/create/{id}', [PayrollController::class, 'payrollCreate']);
    Route::delete('/payroll-type/destroy/{id}', [PayrollController::class, 'payrollDestroy'])->name('payroll_type.destroy');

    Route::get('/employee-salary-structure', [PayrollController::class, 'employeeSalaryStructure'])->name('employee_salary_structure.index');
    Route::post('/employee-salary-structure', [PayrollController::class, 'employeeSalaryStructure'])->name('payroll.show_employee_salary_structure');
    Route::get('/roll-over', [PayrollController::class, 'rollOver'])->name('employee_salary_structure.rollover');
    Route::post('/employee-salary-structure/store', [PayrollController::class, 'employeeSalaryStructureStore'])->name('employee_salary_structure.store');
    Route::post('/rollover-employee-salary-structure/store', [PayrollController::class, 'rolloverEmployeeSalaryStructure'])->name('rollover_employee_salary_structure.store');

    Route::get('setup-institute-details', [dashboardController::class, 'setup_details'])->name('setup-institute-details');

    Route::get('/salary-structure-report', [PayrollController::class, 'salaryStructureReport'])->name('salary_structure_report.index');
    Route::post('/salary-structure-report', [PayrollController::class, 'showSalaryStructureReport']);

    Route::get('/form16', [PayrollController::class, 'form16'])->name('form16.index');
    Route::post('/form16', [PayrollController::class, 'form16Report']);

    /*Route::get('/payroll-deduction', [PayrollController::class, 'payrollDeduction']);
    Route::post('/payroll-deduction', [PayrollController::class, 'payrollDeductionReport']);
    Route::get('/payroll-deduction/{id}', [PayrollController::class, 'payrollDeduction']);*/

    Route::get('/monthly-payroll-report', [PayrollController::class, 'monthlyPayrollReport'])->name('monthly_payroll_report.index');
    Route::post('/monthly-payroll-report', [PayrollController::class, 'monthlyPayrollReport'])->name('payroll.store_monthly_payroll_report');

    Route::post('/show-monthly-payroll-report', [PayrollController::class, 'monthlyPayrollReport'])->name('payroll.show_monthly_payroll_report');
    Route::get('/monthly-payroll-report/pdf/{id}', [PayrollController::class, 'monthlyPayrollPdf']);

    Route::get('/payroll-report', [PayrollController::class, 'payrollReport'])->name('payroll_report.index');
    Route::post('/payroll-report', [PayrollController::class, 'payrollReport'])->name('payroll.show_payroll_report');

    Route::get('/employee-payroll-history', [PayrollController::class, 'employeePayrollHistory'])->name('employee_payroll_history.index');
    Route::post('/employee-payroll-history', [PayrollController::class, 'employeePayrollHistory'])->name('payroll.show_employee_payroll_history');

    Route::get('/payroll-bank-wise-report', [PayrollController::class, 'payrollBankWiseReport'])->name('payroll_bankwise_report.index');
    Route::post('/payroll-bank-wise-report', [PayrollController::class, 'payrollBankWiseReport'])->name('payroll.show_payroll_bankwise_report');

    Route::get('hrms-job-title', [HrmsController::class, 'hrmsJobTitle']);
    Route::get('/hrms-job-title/create', [HrmsController::class, 'hrmsCreate'])->name('hrms_job_title.create');
    Route::get('/hrms-job-title/create/{id}', [HrmsController::class, 'hrmsCreate']);
    Route::post('/hrms-job-title/store', [HrmsController::class, 'hrmsStore'])->name('hrms_job_title.store');
    Route::delete('/hrms-job-title/destroy/{id}', [HrmsController::class, 'hrmsDestroy'])->name('hrms_job_title.destroy');
    Route::get('departmentwise-emplist', [AJAXController::class, 'getDepEmployeeLists'])->name('departmentwise-emplist');
    Route::get('hrms-inout-time', [HrmsController::class, 'hrmsInOutTime'])->name('hrms_inout_time.index');
    Route::post('hrms-in-time/store', [HrmsController::class, 'hrmsInTimeStore'])->name('hrms_in_time.store');
    Route::post('hrms-out-time/store', [HrmsController::class, 'hrmsOutTimeStore'])->name('hrms_out_time.store');

    Route::get('hrms-attendance', [HrmsController::class, 'hrmsAttendance'])->name('hrms_attendance.index');
    Route::post('hrms-attendance-in-time/store', [HrmsController::class, 'hrmsAttendanceInTimeStore'])->name('hrms_attendance_in_time.store');
    Route::post('hrms-attendance-out-time/store', [HrmsController::class, 'hrmsAttendanceOutTimeStore'])->name('hrms_attendance_out_time.store');

    Route::get('hrms-attendance-report', [HrmsController::class, 'hrmsAttendanceReport'])->name('hrms_attendance_report.index');
    Route::post('/hrms-attendance-report', [HrmsController::class, 'hrmsAttendanceReport'])->name('hrms.show_hrms_attendance_report');

    Route::get('early-going-hrms-attendance-report', [HrmsController::class, 'earlyGoingHrmsAttendanceReport'])->name('hrms_attendance_report.early_going_report');
    Route::post('/early-going-hrms-attendance-report', [HrmsController::class, 'earlyGoingHrmsAttendanceReport'])->name('hrms.show_early_going_hrms_attendance_report');

    Route::resource('naac_master', sqaa_controller::class);

    Route::get('insert_naac_master', [sqaa_controller::class, 'InsertNaccMasterView'])->name('insert_naac_master.index');
    Route::post('insert_naac_master_store', [sqaa_controller::class, 'InsertNaccMaster'])->name('insert_naac_master.store');
    Route::get('naac_details', [sqaa_controller::class, 'naac_details'])->name('naac_details.index');

    Route::get('naac_doc_master', [sqaa_controller::class, 'DocTitleMasterView'])->name('naac_doc_master.index');
    Route::post('naac_doc_master_store', [sqaa_controller::class, 'DocTitleMaster'])->name('naac_doc_master.store');

    Route::resource('naac_parts', sqaaPartsController::class);
    Route::post('naac_partA2', [sqaaPartsController::class, 'naacPartA2'])->name('naac_parts2.store');
    Route::put('naac_partA2_update/{id}', [sqaaPartsController::class, 'naacPartA2Update'])->name('naac_parts2.update');

    Route::get('naac_partA3', [sqaaPartsController::class, 'naacPartA3'])->name('naac_parts3.index');
    Route::post('naac_partA3_store', [sqaaPartsController::class, 'naacPartA3Store'])->name('naac_parts3.store');
    Route::put('naac_partA3_update/{id}', [sqaaPartsController::class, 'naacPartA3Update'])->name('naac_parts3.update');

    Route::resource('naac_score_report', sqaaScoreReportController::class);
    Route::resource('naac_report_master', sqaaReportController::class);
    Route::post('naac_detail_update', [sqaaReportController::class, 'naacDetailUpdate'])->name('naac_detail_update.update');
    Route::get('naac_report_master/{id}/edit', 'sqaaReportController@edit')->name('naac_report_master.edit');
    Route::put('naac_report_master/{id}', 'sqaaReportController@update')->name('naac_report_master.update');

    Route::get('get-level', [sqaa_controller::class, 'get_level'])->name('get-level');
    Route::get('gen-pdf', [sqaa_controller::class, 'edit_gen_pdf'])->name('gen-pdf');
    Route::post('gen-pdf-down', [sqaa_controller::class, 'edit_gen_pdf'])->name('gen-pdf-down');
    Route::post('unlink-file', [sqaa_controller::class, 'unlink_file'])->name('unlink-file');

    Route::POST('download-pdf', [sqaa_controller::class, 'generatePdf'])->name('download-pdf');

    Route::resource('questionExcelDownload', questionExcelDownloadController::class);

    Route::get('/whatsapp-user-details', [WhatsappController::class, 'whatsapp_user_details'])->name('whatsapp_user_details.index');
    Route::get('/whatsapp-user-details/create', [WhatsappController::class, 'whatsappUserDetailsCreate'])->name('whatsapp_user_details.create');
    Route::get('/whatsapp-user-details/create/{id}', [WhatsappController::class, 'whatsappUserDetailsCreate']);
    Route::post('/whatsapp-user-details/store', [WhatsappController::class, 'whatsappUserDetailsStore'])->name('whatsapp_user_details.store');
    Route::delete('/whatsapp-user-details/destroy/{id}', [WhatsappController::class, 'whatsappUserDetailsDestroy'])->name('whatsapp_user_details.destroy');

    Route::get('/whatsapp-send-messages', [WhatsappController::class, 'whatsapp_send_messages'])->name('whatsapp_send_messages.index');
    Route::get('/whatsapp-send-messages/create', [WhatsappController::class, 'index'])->name('whatsapp_send_message.create');
    Route::get('/whatsapp-send-messages/add', [WhatsappController::class, 'create'])->name('send_whatsapp_message.create');

    Route::get('/whatsapp-send-messages/create/{id}', [WhatsappController::class, 'whatsappSendMessageCreate']);
    Route::post('/whatsapp-send-messages/store', [WhatsappController::class, 'whatsappSendMessageStore'])->name('send_whatsapp_message.store');
    Route::post('/whatsapp-send-reply-messages/store', [WhatsappController::class, 'whatsappSendReplyMessageStore'])->name('send_whatsapp_reply_message.store');

    Route::delete('/whatsapp-send-messages/destroy/{id}', [WhatsappController::class, 'whatsappSendMessageDestroy'])->name('whatsapp_send_message.destroy');
    Route::get('/whatsapp-sent-generate-report', [WhatsappController::class, 'whatsappSentGenerateReport'])->name('whatsapp_send_messages.generate_report');
    Route::post('/whatsapp-sent-generate-show-report', [WhatsappController::class, 'whatsappSentGenerateReportDetails'])->name('whatsapp_sent_generate_report_details');
    Route::get('/whatsapp-show-reply/{wid}', [WhatsappController::class, 'whatsappShowReply'])->name('whatsapp_show_reply');
});

Route::get('/import-data', [ImportController::class, 'getImport'])->name('import.data');
Route::get('/marks-import', [ImportController::class, 'Import']);
Route::post('/custom_import_parse', [ImportController::class, 'customParseImport'])->name('custom_import_parse');
Route::post('/import_parse', [ImportController::class, 'parseImport'])->name('import_parse');
Route::post('/import_parse_fields', [ImportController::class, 'matchFields'])->name('update.match_fields');
Route::post('/import_process', [ImportController::class, 'processImport'])->name('import_process');

Route::any('/knowledge-base', [dashboardController::class, 'knowledge_base'])->name('knowledge_base')->middleware('session', 'menu');

Route::any('/knowledge-base-detail/{id}/{title}', [dashboardController::class, 'knowledge_base_detail'])->name('knowledge_base_detail')->middleware('session', 'menu');

Route::get('dashboard', [dashboardController::class, 'index'])->name('dashboard')->middleware('session', 'menu', 'logRoute','check_permissions');
// add by uma 
Route::resource('norm-clature', normClatureController::class);
// Route::resource('add-institute-details', institute_detail::class);

// From Build
Route::get('formbuilder/list', [UserFormbuilderController::class, 'index'])->name('formbuild.list')->middleware('session', 'menu', 'logRoute','check_permissions');
Route::get('formbuilder/create', [UserFormbuilderController::class, 'formbuilder'])->name('formbuild.create')->middleware('session', 'menu', 'logRoute','check_permissions');
Route::post('saveformbuild/{id?}', [UserFormbuilderController::class, 'saveformbuilder'])->name('saveformbuild')->middleware('session', 'menu', 'logRoute','check_permissions');
Route::get('formbuilder/edit/{id?}', [UserFormbuilderController::class, 'editformbuilder'])->name('formbuild.edit')->middleware('session', 'menu', 'logRoute','check_permissions');
Route::get('formbuilder/delete/{id?}', [UserFormbuilderController::class, 'deleteformbuilder'])->name('formbuild.delete')->middleware('session', 'menu', 'logRoute','check_permissions');
Route::get('getformbuilder/{name?}', [UserFormbuilderController::class, 'getformbuilder'])->name('getformbuild');
Route::get('apiGetformbuilder/{name?}', [UserFormbuilderController::class, 'Apigetformbuilder'])->name('getformbuild');

# submit form
Route::post('submit_form_data', [UserFormbuilderController::class, 'submitFrom'])->name('submit_form_data');

# view form
Route::get('view_form/{id}/{chapter_id?}', [UserFormbuilderController::class, 'viewForm'])->name('view_form');

# Display form view table
Route::get('displayForm', [UserFormbuilderController::class, 'displayFormDataRecord']);

//End From Build

Route::get('chart_dashboard', [dashboardController::class, 'chart'])->name('chart_dashboard')->middleware('session', 'menu', 'logRoute','check_permissions');

Route::get('schoolList', [dashboardController::class, 'schoolList'])->name('schoolList');

Route::get('siteMap', [dashboardController::class, 'siteMap'])->name('siteMap')->middleware('session', 'menu', 'logRoute','check_permissions');

Route::any('login', [loginController::class, 'index'])->name('login');
Route::any('signup', [signupController::class, 'index'])->name('signup');
//Route::get('aftersignuplogin', 'loginController@aftersignuplogin');

Route::any('ajaxMenuSession', [loginController::class, 'ajaxMenuSession'])->name('ajaxMenuSession');

Route::any('logout', [loginController::class, 'logout'])->name('logout');

Route::any('/profileAPI', [loginController::class, 'profileAPI']);

Route::any('/tourUpdate', [tourController::class, 'index'])->name('tourUpdate');

Route::get('/implementation', [tourController::class, 'implementation'])->name('implementation')->middleware('session', 'menu', 'logRoute','check_permissions');
Route::get('/Onboarding', [tourController::class, 'Onboarding'])->name('Onboarding')->middleware('session', 'menu', 'logRoute','check_permissions');
Route::get('/implementation_1', [tourController::class, 'implementation_1'])->name('implementation_1')->middleware('session', 'menu', 'logRoute','check_permissions');
Route::get('/implementation_2', [tourController::class, 'implementation_2'])->name('implementation_2')->middleware('session', 'menu', 'logRoute','check_permissions');
Route::get('/skip_implementation', [tourController::class, 'skipImplementation'])->name('skip_implementation')->middleware('session', 'menu', 'logRoute','check_permissions');

Route::get('ajax_SaveDynamicDashboardMenu', [dashboardSettingController::class, 'ajax_SaveDynamicDashboardMenu'])->name('ajax_SaveDynamicDashboardMenu');

// Harshad Start
Route::group(['prefix' => 'student', 'middleware' => ['session', 'menu', 'logRoute','check_permissions']], function () {
    Route::get('studentresult', [TemplateResult::class, 'index']);
    Route::post('studentresult_show', [TemplateResult::class, 'show_result'])->name('studentresult.show');
    Route::get('result_show/{arr?}', [TemplateResult::class, 'result_show'])->name('result.show');
});
// Harshad End


Route::group(['prefix' => 'school_setup', 'middleware' => ['session', 'menu', 'logRoute','check_permissions']], function () {
    Route::resource('add_school', schoolController::class);
    Route::resource('std_div_map', std_divController::class);
    Route::resource('division_capacity_master', divisionCapacityMasterController::class);
    Route::resource('subject_master', subject1Controller::class);
    Route::resource('chapter_master', chapterController::class);
    Route::get('ajax_StandardwiseSubject', [chapterController::class, 'StandardwiseSubject'])->name('ajax_StandardwiseSubject');
    Route::resource('topic_master', topicController::class);
    Route::resource('sub_std_map', sub_std_mapController::class);
    Route::resource('period_master', periodController::class);
    Route::resource('change_password', changePasswordController::class);
    Route::resource('dashboard_setting', dashboardSettingController::class);
    Route::get('device_check', [changePasswordController::class, 'device_check'])->name('device_check');

    Route::resource('erp_status', erpstatusController::class);
    Route::resource('workflow', workflowController::class);
    Route::get('ajax_wk_modulewise_fields', [workflowController::class, 'wk_modulewise_fields'])->name('ajax_wk_modulewise_fields');
    Route::post('ajax_wk_savemail', [workflowController::class, 'wk_savemail'])->name('ajax_wk_savemail');
    Route::post('ajax_wk_saveupdatequery', [workflowController::class, 'wk_saveupdatequery'])->name('ajax_wk_saveupdatequery');
    Route::post('ajax_wk_savesms', [workflowController::class, 'wk_savesms'])->name('ajax_wk_savesms');

    Route::resource('batch_master', batchController::class);
    Route::post('ajaxdestroybatch_master', [batchController::class, 'ajaxdestroy'])->name('ajaxdestroybatch_master');
    Route::get('ajax_StandardwiseDivision', [batchController::class, 'StandardwiseDivision'])->name('ajax_StandardwiseDivision');

    Route::resource('timetable', timetableController::class);
    Route::get('ajax_AcademicwiseStandard', [timetableController::class, 'AcademicwiseStandard'])->name('ajax_AcademicwiseStandard');
    Route::post('ajax_getTimetable', [timetableController::class, 'getTimetable'])->name('ajax_getTimetable');

    Route::post('insert_data', [subject1Controller::class, 'insert_data'])->name('insert_data');

    // Route::post('/school_setup/subject_master/insert_data','school_setup\subject1Controller@insert_data');

    Route::resource('classwisetimetable', classwisetimetableController::class);
    Route::post('ajax_getClasswiseTimetable', [classwisetimetableController::class, 'getClasswiseTimetable'])->name('ajax_getClasswiseTimetable');
    Route::get('ajax_Batch_Timetable', [timetableController::class, 'getBatchTimetable'])->name('ajax_Batch_Timetable');

    Route::get('ajax_New_Standard_Div', [timetableController::class, 'getNewStandardDiv'])->name('ajax_New_Standard_Div');

    Route::get('ajax_Delete_Timetable', [timetableController::class, 'deleteTimetable'])->name('ajax_Delete_Timetable');

    Route::get('ajax_Mapping_Teachers', [timetableController::class, 'getMappingTeachers'])->name('ajax_Mapping_Teachers');

    Route::resource('proxy_master', proxyController::class);

    Route::post('ajax_getproxyperiod', [proxyController::class, 'getproxyperiod'])->name('ajax_getproxyperiod');
    Route::resource('facultywisetimetable', facultywisetimetableController::class);
    Route::post('ajax_getFacultywiseTimetable', [facultywisetimetableController::class, 'getFacultywiseTimetable'])->name('ajax_getFacultywiseTimetable');

    Route::resource('proxy_report', proxyReportController::class);
    Route::post('ajax_getproxyreport', [proxyReportController::class, 'getproxyreport'])->name('ajax_getproxyreport');

    Route::resource('todays_proxy_report', todaysproxyReportController::class);

    Route::resource('classteacher', classteacherController::class);
    Route::resource('classteacherReport', classteacherReportController::class);

    Route::resource('teachertransfer', teachertransferController::class);

    Route::resource('teacher_daily_report', teacherdailyReportController::class);
    Route::post('ajax_getTeacherDailyReport', [teacherdailyReportController::class, 'getTeacherDailyReport'])->name('ajax_getTeacherDailyReport');

    Route::resource('lessonplanning', lessonplanningController::class);
    Route::get('ajax_getlp_subject', [lessonplanningController::class, 'getSubjectData'])->name('ajax_getlp_subject');
    Route::get('ajax_getlp_division', [lessonplanningController::class, 'getDivisionData'])->name('ajax_getlp_division');

    Route::resource('lessonplanningReport', lessonplanningReportController::class);

    Route::get('google-analytics-summary', array('as' => 'google-analytics-summary', 'uses' => 'school_setup\HomeController@getAnalyticsSummary'));

    Route::resource('used_storage_graph', used_storage_graphController::class);
});
Route::post('get_proxy_master', [proxyController::class, 'getproxydata']);
Route::get('school_setup/ajax_getTeacherDailyDetailsReport', [teacherdailyReportController::class, 'getTeacherDailyDetailsReport'])->name("ajax_getTeacherDailyDetailsReport");


Route::post('/teacherTimetableAPI', [facultywisetimetableController::class, 'teacherTimetableAPI']);
Route::post('/studentTimetableAPI', [classwisetimetableController::class, 'studentTimetableAPI']);

Route::get('ajax_load_rightSideMenu', [AJAXController::class, 'ajax_load_rightSideMenu'])->name('ajax_load_rightSideMenu');
Route::get('ajax_load_helpguide', [AJAXController::class, 'ajax_load_helpguide'])->name('ajax_load_helpguide');

Route::post('ajax_sendmail', [AJAXController::class, 'ajax_sendmail'])->name('ajax_sendmail');

Route::post('collectsct', [AJAXController::class, 'collectsct'])->name('collectsct');


Route::get('ckeditor/create', [CkeditorFileUploadController::class, 'create'])->name('ckeditor.create');
Route::post('ckeditor', [CkeditorFileUploadController::class, 'store'])->name('uploadimage');

// Route::get('exception/index', 'ExceptionController@index');

//Auth::routes();

// Route::get('/home', 'HomeController@index')->name('home');

// Forgot Password Routes
Route::get('forget-password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forget.password.get');
Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post');
Route::get('reset-password/{token}/{email}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');


Route::post('NewLMS_temp_signup', [NewLMS_ApiController::class, 'NewLMS_temp_signup'])->name("NewLMS_temp_signup");
Route::post('NewLMS_signup', [NewLMS_ApiController::class, 'NewLMS_signup'])->name("NewLMS_signup");
Route::get('Resend_otp', [NewLMS_ApiController::class, 'Resend_otp'])->name("Resend_otp");
Route::post('preload-institute', [NewLMS_ApiController::class, 'Preload_institute'])->name("preload-institute");
Route::post('add-institute', [NewLMS_ApiController::class, 'add_institute'])->name("add-institute");

Route::post('NewLMS_temp_signup_student', [NewLMS_StudentApiController::class, 'NewLMS_temp_signup_student'])->name("NewLMS_temp_signup_student");
Route::post('NewLMS_signup_student', [NewLMS_StudentApiController::class, 'NewLMS_signup_student'])->name("NewLMS_signup_student");


Route::get('get_trizStandard', [signupController::class, 'get_trizStandard'])->name("get_trizStandard");

Route::post('searching_menu', [AJAXController::class, 'searchMenu'])->name("searching_menu");
Route::post('get_search_url', [AJAXController::class, 'get_search_url'])->name("get_search_url");


// send birthday notification
Route::get(
    'send_birthday_notification',
    [send_birthday_notification_controller::class, 'send_birthday_notification']
)->name('send_birthday_notification');

// Question Wise Report
Route::get('/questionReport', [questionWiseReportController::class, 'index'])->name('question_wise_report');
Route::post(
    '/show_question_wise_report',
    [questionWiseReportController::class, 'show_question_wise_report']
)->name('show_question_wise_report');

// Admin Authorization (Show Result)
Route::GET('result_admin_permission', 'result\result_admin_permission\ResultAdminPermissionController@index')->name('result_admin_permission');
Route::POST('show_result_admin_permission', 'result\result_admin_permission\ResultAdminPermissionController@show_result_admin_permission')->name('show_result_admin_permission');
Route::POST('allow_admin_permission', 'result\result_admin_permission\ResultAdminPermissionController@allow_admin_permission')->name('allow_admin_permission');


// create result excel
Route::get('download_create_result', 'result\MarkUploadController@index');
Route::post('generate_create_result_excel', 'result\MarkUploadController@create')->name('create-excel');
Route::get('upload_create_result', 'result\MarkUploadController@store')->name('upload_create_result');

Route::get('fetch_payment_status', 'fees\online_fees\online_fees_collect_controller@razorpay_fetch_payment_status');
Route::get('icici_fetch_payment_status', 'fees\online_fees\online_fees_collect_controller@icici_fetch_payment_status');
Route::get('payphi_fetch_payment_status', 'fees\online_fees\online_fees_collect_controller@payphi_fetch_payment_status');

Route::group(['middleware' => ['session', 'menu', 'logRoute','check_permissions']], function () {
    Route::resource('leave-type', LeaveTypeController::class);
    Route::resource('holiday', HolidayController::class);
    Route::resource('leave-apply', ApplyLeaveController::class);
    Route::get('my-leave', [ApplyLeaveController::class, 'myLeave'])->name('my-leave');
    Route::get('import-leave', [ApplyLeaveController::class, 'importLeave'])->name('import-leave');
    Route::post('import-leave', [ApplyLeaveController::class, 'importOldLeave'])->name('import-leave');
    Route::get('holiday.weekdays', [HolidayController::class, 'getWeekdays'])->name('holiday.weekdays');
    Route::post('holiday.weekdays', [HolidayController::class, 'storeWeekdays'])->name('holiday.weekdays');

    Route::resource('books', BookController::class);
    // Route::resource('item_verification_status', itemVerificationController::class);
    // Route::resource('scan_books', itemScanController::class);
    // Route::get('scan_books_remarks', [itemScanController::class, 'remarksIndex'])->name('scan_books_remarks.index');
    // Route::post('scan_books_remarks/store', [itemScanController::class, 'remarksStore'])->name('scan_books_remarks.store');
    // Route::get('verified_book_report', [itemScanController::class, 'verifiedReport'])->name('verified_report.index');
    // Route::get('verified_book_report_pending', [itemScanController::class, 'verifyPendingReport'])->name('verifiyPending_report.index');

    Route::get('books/{id}/barcode', [BookController::class, 'generateBarcode'])->name('books.barcode');
    Route::get('books/{id}/reutrn', [BookController::class, 'returnBook'])->name('books.return');
    Route::post('books/issue', [BookController::class, 'issueBook'])->name('books.issue');
    Route::get('books.circulation', [BookController::class, 'circulation'])->name('books.circulation');
    Route::get('books/{id}/item', [BookController::class, 'item'])->name('books.item');
    Route::delete('books/{id}/item/delete', [BookController::class, 'deleteItem'])->name('books.items.destroy');
    Route::get('quick_return', [BookController::class, 'QuickReturn'])->name('quick_return.index');
    Route::post('quick_return', [BookController::class, 'QuickReturnSearch'])->name('quick_return.create');
    Route::get('check_issue', [BookController::class, 'checkIssue'])->name('check_issue');
    // api 
    Route::get('all_book_lists', [BookController::class, 'allBookLists'])->name('allBookLists.index');

    Route::resource('library_report', LibraryReportController::class);
    Route::post('show_library_report', [LibraryReportController::class, 'show_library_report'])->name('show_library_report');

    Route::get('book_issue_report', [LibraryReportController::class, 'bookIssueDueReport'])->name('book_issue_report.index');
    Route::post('book_issue_report', [LibraryReportController::class, 'bookIssueDueReportCreate'])->name('book_issue_report.create');

    Route::get('print_barcode', [LibraryReportController::class, 'PrintBarcode'])->name('print_barcode.index');
    Route::post('print_barcode', [LibraryReportController::class, 'PrintBarcodeCreate'])->name('print_barcode.create');
    Route::post('generateBarcodePdf', [LibraryReportController::class, 'generateBarcodePdf'])->name('generateBarcodePdf');
});

Route::group(['prefix' => 'attendance', 'middleware' => ['session', 'menu', 'logRoute','check_permissions']], function () {
    // attendanceController
    Route::resource('students_attendance', attendanceController::class);
    Route::resource('semwise_report', attendanceReportController::class);
    Route::resource('monthwise_attendance_report', monthwiseAttendanceReportController::class);
});
Route::get('get-lecture-list', [AJAXController::class, 'getLectureList']);
Route::get('get-batch', [AJAXController::class, 'get_batch']);

Route::get('api/get-standard-list', [AJAXController::class, 'getStandardList']);
Route::get('api/get-division-list', [AJAXController::class, 'getDivisionList']);
Route::get('api/get-subject-list', [AJAXController::class, 'getSubjectList']);
Route::get('api/get-subject-list-timetable', [AJAXController::class, 'getSubjectListTimetable']);
Route::get('api/get-batch-list-timetable', [AJAXController::class, 'getBatchTimetable'])->name('getBatchTimetable');
/** get exam list */
Route::get('api/get-exam-name-list', [AJAXController::class, 'getExamsList']);
Route::get('api/get-exam-master-list', [AJAXController::class, 'getExamsMasterList']);

/** get subjec by create exam list */
Route::get('api/get-subject-by-create-exam', [AJAXController::class, 'getSubjectByCreateExam']);
/** get Exam by create exam list */
Route::get('api/get-exam-name-by-create-exam', [AJAXController::class, 'getExamByCreateExam']);
Route::get('api/get-chapter-list', [AJAXController::class, 'getChapterList']);
Route::get('api/get-topic-list', [AJAXController::class, 'getTopicList']);
Route::get('api/get-exam-list', [AJAXController::class, 'getExamList']);
Route::get('api/get-co-scholastic-parent-list', [AJAXController::class, 'getCoScholasticParentList']);
Route::get('api/get-co-scholastic-list', [AJAXController::class, 'getCoScholasticList']);

Route::GET('ajax_sendEmailFeesReceipt', [AJAXController::class, 'ajax_sendEmailFeesReceipt'])->name('ajax_sendEmailFeesReceipt');
Route::GET('ajax_sendBulkEmailFeesReceipt', [AJAXController::class, 'ajax_sendBulkEmailFeesReceipt'])->name('ajax_sendBulkEmailFeesReceipt');

// 2024-12-09
Route::post('transferDocs', [oldDocumentTransfer::class, 'storeImagesToDigitalOcean']);
Route::get('convertDoc', [oldDocumentTransfer::class, 'ConvertBinaryData']);

Route::get('privacyPolicy', [dashboardController::class, 'privacyPolicy'])->name('privacyPolicy');
Route::get('termAndCondition', [dashboardController::class, 'termAndCondition'])->name('termAndCondition');
Route::get('otherPolicy', [dashboardController::class, 'otherPolicy'])->name('otherPolicy');
Route::any('check_permissions', [AJAXController::class, 'check_access'])->name('check_permissions');
Route::any('check_access', [AJAXController::class, 'check_access'])->name('check_access');

Route::any('chat', [AJAXController::class, 'chat'])->name('chat');
Route::get('studentLists', [AJAXController::class, 'studentLists'])->name('studentLists');
Route::post('sendSMSCommon',[send_sms_parents_controller::class,'send_SMS_Common'])->name('sendSMSCommon.store');