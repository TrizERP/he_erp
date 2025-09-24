<?php

namespace App\Http\Controllers;

use App\Http\Controllers\fees\fees_report\otherNewfeesReportController;
use App\Models\tblmenumasterModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer;
use function App\Helpers\FeeBreackoff;
use function App\Helpers\FeeBreakoffHeadWise;
use function App\Helpers\FeeMonthId;
use function App\Helpers\htmlToPDF;
use function App\Helpers\htmlToPDFLandscape;
use function App\Helpers\htmlToPDFLandscapeCertificate;
use function App\Helpers\htmlToPDFPortrait;
use function App\Helpers\OtherBreackOff;
use function App\Helpers\SearchStudent;
use function App\Helpers\OtherBreackOffHead;
// use function App\Helpers\OtherBreackOffHeadlast;
use function App\Helpers\OtherBreackOfMonth;
use App\Models\school_setup\standardModel;
use App\Models\school_setup\divisionModel;
use App\Models\school_setup\academic_sectionModel;
use function App\Helpers\get_string;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use function App\Helpers\is_mobile;
use function App\Helpers\getSubCordinates;

class AJAXController extends Controller
{
    /**
     * GET Exam By Search
     * @field grade_id, standard, division, subject, sub_institute_id, syear
     */
    public function getExamsList(Request $request)
    {
        $grade = $request->grade_id;
        $standard = $request->standard_id;
        $division = $request->division_id;
        $subject = $request->subject_id;
        $sub_institute_id = $request->sub_institute_id;
        $syear = $request->syear;

        $queryResult = DB::table('tblstudent as ts')
            ->join('tblstudent_enrollment as tse', function ($join) {
                $join->whereRaw('tse.student_id = ts.id');
            })->join('academic_year as ay', function ($join) {
                $join->whereRaw('ay.term_id = tse.term_id');
            })->join('standard as std', function ($join) {
                $join->whereRaw('std.id = tse.standard_id');
            })->join('std_div_map as sdm', function ($join) {
                $join->whereRaw('sdm.standard_id = std.id');
            })->join('division as divi', function ($join) {
                $join->whereRaw('divi.id = sdm.division_id');
            })->join('sub_std_map as ssm', function ($join) {
                $join->whereRaw('ssm.standard_id = sdm.standard_id');
            })->join('subject as sub', function ($join) {
                $join->whereRaw('sub.id = ssm.subject_id');
            })->join('question_paper as qp', function ($join) {
                $join->whereRaw('qp.subject_id = ssm.subject_id and qp.standard_id = sdm.standard_id');
            })->join('lms_question_master as lqm', function ($join) {
                $join->whereRaw('lqm.subject_id = ssm.subject_id and lqm.standard_id = sdm.standard_id and lqm.id in
                (SELECT lqm.id FROM lms_question_master as lqm,question_paper as qp2 WHERE qp.id = qp2.id AND FIND_IN_SET(lqm.id, qp.question_ids))');
            })->join('lms_online_exam_answer as am', function ($join) {
                $join->whereRaw('am.question_paper_id = qp.id and am.student_id = ts.id and am.question_id = lqm.id');
            })
            ->selectRaw('qp.id, am.online_exam_id AS online_exam_ids, qp.paper_name AS question_paper_name')
            ->where('std.grade_id', '=', $grade)
            ->where('std.id', '=', $standard)
            ->where('divi.id', '=', $division)
            ->where('sub.id', '=', $subject)
            ->where('ts.sub_institute_id', '=', $sub_institute_id)
            ->where('ay.syear', '=', $syear)
            ->where('ay.sub_institute_id', '=', $sub_institute_id)
            ->groupBy('qp.id')->get()->toArray();

        return response()->json($queryResult);
    }

    /**
     * get Subject name by Create Exam
     */
    public function getSubjectByCreateExam(Request $request)
    {
        $grade = $request->grade_id;
        $standard = $request->standard_id;
        $division = $request->division_id;
        $sub_institute_id = $request->sub_institute_id;
        $syear = $request->syear;

        $queryResult = DB::table('tblstudent as ts')
            ->join('tblstudent_enrollment as tse', function ($join) {
                $join->whereRaw('tse.student_id = ts.id');
            })->join('academic_year as ay', function ($join) {
                $join->whereRaw('ay.term_id = tse.term_id');
            })->join('standard as std', function ($join) {
                $join->whereRaw('std.id = tse.standard_id');
            })->join('std_div_map as sdm', function ($join) {
                $join->whereRaw('sdm.standard_id = std.id');
            })->join('division as divi', function ($join) {
                $join->whereRaw('divi.id = sdm.division_id');
            })->join('result_create_exam AS rce', function ($join) {
                $join->whereRaw('rce.standard_id = sdm.standard_id');
            })->join('subject as sub', function ($join) {
                $join->whereRaw('sub.id = rce.subject_id');
            })
            ->selectRaw('sub.subject_name as subject_name,rce.subject_id as subject_id')
            ->where('std.grade_id', '=', $grade)
            ->where('std.id', '=', $standard)
            ->where('divi.id', '=', $division)
            ->where('ts.sub_institute_id', '=', $sub_institute_id)
            ->where('ay.syear', '=', $syear)
            ->where('ay.sub_institute_id', '=', $sub_institute_id)
            ->groupBy('rce.subject_id')->get()->toArray();

        return response()->json($queryResult);
    }

    /**
     * get Exam name by Create Exam
     */
    public function getExamByCreateExam(Request $request)
    {
        $grade = $request->grade_id;
        $standard = $request->standard_id;
        $subject = $request->subject_id;
        $division = $request->division_id;
        $sub_institute_id = $request->sub_institute_id;
        $syear = $request->syear;

        $queryResult = DB::table('tblstudent as ts')
            ->join('tblstudent_enrollment as tse', function ($join) {
                $join->whereRaw('tse.student_id = ts.id');
            })->join('academic_year as ay', function ($join) {
                $join->whereRaw('ay.term_id = tse.term_id');
            })->join('standard as std', function ($join) {
                $join->whereRaw('std.id = tse.standard_id');
            })->join('std_div_map as sdm', function ($join) {
                $join->whereRaw('sdm.standard_id = std.id');
            })->join('division as divi', function ($join) {
                $join->whereRaw('divi.id = sdm.division_id');
            })->join('result_create_exam AS rce', function ($join) {
                $join->whereRaw('rce.standard_id = sdm.standard_id');
            })->join('subject as sub', function ($join) {
                $join->whereRaw('sub.id = rce.subject_id');
            })->join('result_exam_master', function ($join) {
                $join->whereRaw('rem.id = rce.exam_id');
            })
            ->selectRaw('sub.subject_name as subject_name,rce.subject_id as subject_id')
            ->where('std.grade_id', '=', $grade)
            ->where('std.id', '=', $standard)
            ->where('divi.id', '=', $division)
            ->where('rce.subject_id', '=', $subject)
            ->where('ts.sub_institute_id', '=', $sub_institute_id)
            ->where('ay.syear', '=', $syear)
            ->where('ay.sub_institute_id', '=', $sub_institute_id)
            ->groupBy('rce.exam_id')->get()->toArray();

        return response()->json($queryResult);
    }

    public function getStandardList(Request $request)
    {
        $path = $_SERVER['HTTP_REFERER'];

        if ($path) {
            $parsedUrl = parse_url($path);

            if (isset($parsedUrl['path'])) {
                $pathParts = pathinfo($parsedUrl['path']);

                if (isset($pathParts['filename'])) {
                    $module_name = $pathParts['filename'];
                }
            }
        }

        $module_array = [
            '1' => 'student_homework',
            '2' => 'marks_entry',
            '3' => 'dicipline',
            '4' => 'lmsExamwise_progress_report',
            '5' => 'questionReport',
            '6' => 'parent_communication',
        ];

        $explode = explode(',', $request->grade_id);

        if (count($explode) > 1) {
            $query = DB::table('standard');
            $query->whereIn('grade_id', $explode)->get();

            //START Check for class teacher assigned standards
            $classTeacherStdArr = session()->get('classTeacherStdArr');

            if (is_array($classTeacherStdArr)) {
                $checkstd = count($classTeacherStdArr) > 0;
            } else {
                $checkstd = '1=1';
            }
            if ($checkstd && $classTeacherStdArr != "" && !in_array($module_name, $module_array)) {
                $query->whereIn('id', $classTeacherStdArr);
            }
            //END Check for class teacher assigned standards

            //START Check for subject teacher assigned
            $subjectTeacherStdArr = session()->get('subjectTeacherStdArr');
            if ($subjectTeacherStdArr != "" && ($classTeacherStdArr == "" || in_array($module_name, $module_array))) {
                $query->whereIn('id', $subjectTeacherStdArr);
            }
            //END Check for subject teacher assigned
            $query->where('marking_period_id', session()->get('term_id')); // added for sem wise on 03-03-2025 by uma

            $standard = $query->pluck("name", "id");
        } else {
            $query = DB::table('standard');
            $query->where("grade_id", $request->grade_id);

            //START Check for class teacher assigned standards
            $classTeacherStdArr = session()->get('classTeacherStdArr');
            if (is_array($classTeacherStdArr)) {
                $checkstd = count($classTeacherStdArr) > 0;
            } else {
                $checkstd = '1=1';
            }
            if ($checkstd && $classTeacherStdArr != "" && !in_array($module_name, $module_array)) {
                $query->whereIn('id', $classTeacherStdArr);
            }
            //END Check for class teacher assigned standards

            //START Check for subject teacher assigned
            $subjectTeacherStdArr = session()->get('subjectTeacherStdArr');
            if ($subjectTeacherStdArr != "" && ($classTeacherStdArr == "" || in_array($module_name, $module_array))) {
                $query->whereIn('id', $subjectTeacherStdArr);
            }
            $query->where('marking_period_id', session()->get('term_id')); // added for sem wise on 03-03-2025 by uma
            //END Check for subject teacher assigned
            $standard = $query->pluck("name", "id");
        }

        return response()->json($standard);
        // return $classTeacherStdArr;
    }

    public function getDivisionList(Request $request)
    {
        $path = $_SERVER['HTTP_REFERER'];

        if ($path) {
            $parsedUrl = parse_url($path);

            if (isset($parsedUrl['path'])) {
                $pathParts = pathinfo($parsedUrl['path']);

                if (isset($pathParts['filename'])) {
                    $module_name = $pathParts['filename'];
                }
            }
        }

        $module_array = [
            '1' => 'student_homework',
            '2' => 'marks_entry',
            '3' => 'dicipline',
            '4' => 'lmsExamwise_progress_report',
            '5' => 'questionReport',
            '6' => 'parent_communication',
        ];

        $standard_id = $request->standard_id;

        $explode = explode(',', $request->standard_id);
        if (count($explode) > 1) {
            $query = DB::table('std_div_map');
            $query->join('division', 'division.id', '=', 'std_div_map.division_id');
            $query->whereIn("std_div_map.standard_id", $explode);

            //START Check for class teacher assigned standards
            $classTeacherDivArr = session()->get('classTeacherDivArr');
            if (is_array($classTeacherDivArr)) {
                $checkdiv = count($classTeacherDivArr) > 0;
            } else {
                $checkdiv = '1=1';
            }
            if ($checkdiv && $classTeacherDivArr != "" && !in_array($module_name, $module_array)) {
                $query->whereIn('division.id', $classTeacherDivArr);
            }
            //END Check for class teacher assigned standards

            //START Check for subject teacher assigned
            $subjectTeacherDivArr = session()->get('subjectTeacherDivArr');
            if ($subjectTeacherDivArr != "" && ($classTeacherDivArr == "" || in_array($module_name, $module_array))) {
                $query->orwhereIn('division.id', $subjectTeacherDivArr);
            }
            //END Check for subject teacher assigned

            $std_div_map = $query->pluck('division.name', 'division.id');
        } else {
            // DB::enableQueryLog();
            $query = DB::table('std_div_map');
            $query->join('division', 'division.id', '=', 'std_div_map.division_id');
            $query->where("std_div_map.standard_id", $request->standard_id);
            //START Check for class teacher assigned standards
            $classTeacherDivArr = session()->get('classTeacherDivArr');
            if (is_array($classTeacherDivArr)) {
                $checkdiv = count($classTeacherDivArr) > 0;
            } else {
                $checkdiv = '1=1';
            }
            if ($checkdiv && $classTeacherDivArr != "" && !in_array($module_name, $module_array)) {
                $query->whereIn('division.id', $classTeacherDivArr);
            }
            //END Check for class teacher assigned standards
            //START Check for class teacher assigned standards
            $subjectTeacherDivArr = session()->get('subjectTeacherDivArr');
            if ($subjectTeacherDivArr != "" && ($classTeacherDivArr == "" || in_array($module_name, $module_array))) {
                $query->whereIn('division.id', function ($sub_query) use ($standard_id) {
                    $sub_query->select('division_id')
                        ->from('timetable')
                        ->where('teacher_id', session()->get('user_id'))
                        ->where('standard_id', $standard_id);
                });
            }
            //END Check for class teacher assigned standards

            $std_div_map = $query->pluck('division.name', 'division.id');
        }

        return response()->json($std_div_map);
    }

    public function getSubjectList(Request $request)
    {
        $standard_id = $request->standard_id;
        $explode = explode(',', $request->standard_id);

        $arr = $request->server;
        $HTTP_REFERER = "";
        foreach ($arr as $id => $val) {
            if ($id == 'HTTP_REFERER') {
                $HTTP_REFERER = $val;
            }
        }
        $refer_arr = explode('/', $HTTP_REFERER);

        if (count($refer_arr) >= 2 && $refer_arr[count($refer_arr) - 2] == 'exam_creation' || in_array('marks_entry', $refer_arr)) {
            $where = array(
                "sub_std_map.sub_institute_id" => session()->get('sub_institute_id'),
                "sub_std_map.allow_grades" => "Yes",
            );
        } else {
            $where = array(
                "sub_std_map.sub_institute_id" => session()->get('sub_institute_id'),
            );
        }
        if (count($explode) > 1) {
            $std_sub_map = DB::table('subject')
                ->join('sub_std_map', 'subject.id', '=', 'sub_std_map.subject_id')
                ->whereIn("sub_std_map.standard_id", $explode)
                ->where($where)
                ->orderBy('sub_std_map.sort_order')
                ->pluck('sub_std_map.display_name', 'subject.id');
        } else {
            if (session()->get('user_profile_name') == 'Lecturer') {
                # Get subjects by teacher, standard and division
                $std_sub_map = DB::table('subject as sub')
                    ->whereIn('sub.id', function ($sub_query) use ($request) {
                        $sub_query->select('subject_id')
                            ->from('timetable')
                            ->where('teacher_id', session()->get('user_id'))
                            ->where('standard_id', $request->standard_id)
                            ->where('division_id', $request->division_id);
                    })
                    ->pluck('sub.subject_name as display_name', 'sub.id');
            } else {
                $where['sub_std_map.standard_id'] = $request->standard_id;
                $std_sub_map = DB::table('subject')
                    ->join('sub_std_map', 'subject.id', '=', 'sub_std_map.subject_id')
                    ->where($where)
                    ->orderBy('sub_std_map.sort_order')
                    ->pluck('sub_std_map.display_name', 'subject.id');
            }
        }

        return response()->json($std_sub_map);
    }

    public function getSubjectListTimetable(Request $request)
{
    $standard_id = $request->standard_id;
    $division_id = $request->division_id;

    $explode = explode(',', $request->standard_id);

    $arr = $request->server;
    $HTTP_REFERER = "";
    foreach ($arr as $id => $val) {
        if ($id == 'HTTP_REFERER') {
            $HTTP_REFERER = $val;
        }
    }
    $refer_arr = explode('/', $HTTP_REFERER);

    if (count($refer_arr) >= 2 && $refer_arr[count($refer_arr) - 2] == 'exam_creation' || in_array('marks_entry', $refer_arr)) {
        $where = array(
            "sub_std_map.sub_institute_id" => session()->get('sub_institute_id'),
            "sub_std_map.allow_grades" => "Yes",
        );
    } else {
        $where = array(
            "sub_std_map.sub_institute_id" => session()->get('sub_institute_id'),
        );
    }
    
    if (count($explode) > 1) {
        $std_sub_map = DB::table('subject')
            ->join('sub_std_map', 'subject.id', '=', 'sub_std_map.subject_id')
            ->whereIn("sub_std_map.standard_id", $explode)
            ->where($where)
            ->orderBy('sub_std_map.sort_order')
            ->pluck('sub_std_map.display_name', 'subject.id');
    } else {
        $todayDay = substr(date('l', strtotime($request->date)), 0, 1); // Get first letter of day
        if(strtolower($todayDay) == 't') { // Handle Tuesday/Thursday ambiguity
            $fullDay = strtolower(date('l', strtotime($request->date)));
            $todayDay = ($fullDay == 'thursday') ? 'H' : 'T';
        }
        
        # Get subjects by teacher, standard and division
        $std_sub_mapArr = DB::table('subject as sub')
            ->join('timetable as t', 't.subject_id', '=', 'sub.id')
            ->join('period as p', 'p.id', '=', 't.period_id')
            ->when(session()->get('user_profile_name') == 'Lecturer', function ($query) {
                $query->where('t.teacher_id', session()->get('user_id'));
            })
            ->where('t.standard_id', $request->standard_id)
            ->where('t.division_id', $request->division_id)
            ->where('t.syear', session()->get('syear'))
            ->where('t.week_day', strtoupper($todayDay))
            ->select(
                'sub.id',
                'sub.subject_name as display_name',
                't.extend_lab',
                't.type',
                't.id as timetable_id',
                't.period_id',
                'p.title as lecture',
                'p.short_name',
                'p.sort_order' // Add sort_order to determine period sequence
            )
            ->orderBy('p.sort_order') // Ensure periods are ordered
            ->get();

        $merged = [];

        // Group by display_name + extend_lab + period_id first
        foreach ($std_sub_mapArr as $value) {
            $key = $value->display_name . "###" . $value->extend_lab . "###" . $value->period_id;

            if (!isset($merged[$key])) {
                $merged[$key] = [
                    'entries' => [], // Store all original entries to maintain alignment
                    'display_name' => $value->display_name,
                    'extend_lab' => $value->extend_lab,
                    'type' => $value->type,
                    'period_id' => $value->period_id,
                    'sort_order' => $value->sort_order,
                    'short_name' => $value->short_name
                ];
            }

            $merged[$key]['entries'][] = [
                'subject_id' => $value->id,
                'period_id' => $value->period_id,
                'sort_order' => $value->sort_order,
                'timetable_id' => $value->timetable_id,
                'short_name' => $value->short_name
            ];
        }

        // Now group by display_name + extend_lab for consecutive period checking
        $grouped_by_subject = [];
        foreach ($merged as $key => $item) {
            $subject_key = $item['display_name'] . "###" . $item['extend_lab'];
            
            if (!isset($grouped_by_subject[$subject_key])) {
                $grouped_by_subject[$subject_key] = [
                    'display_name' => $item['display_name'],
                    'extend_lab' => $item['extend_lab'],
                    'type' => $item['type'],
                    'periods' => []
                ];
            }
            
            // Add the period entry (already merged by period_id)
            $grouped_by_subject[$subject_key]['periods'][] = [
                'subject_id' => $item['entries'][0]['subject_id'], // Use first entry's subject_id
                'period_id' => $item['period_id'],
                'sort_order' => $item['sort_order'],
                'timetable_ids' => array_column($item['entries'], 'timetable_id'), // All timetable_ids
                'short_name' => $item['short_name']
            ];
        }

        // Step 3: Process each subject group to find consecutive periods
        $std_sub_map = [];
        foreach ($grouped_by_subject as $subject_key => $item) {
            // Sort periods by sort_order
            usort($item['periods'], function($a, $b) {
                return $a['sort_order'] <=> $b['sort_order'];
            });
            
            // Group consecutive periods
            $consecutive_groups = $this->groupConsecutivePeriods($item['periods']);
            
            foreach ($consecutive_groups as $group) {
                if (count($group) > 1) {
                    // Multiple consecutive periods - create range
                    $first = reset($group);
                    $last = end($group);
                    $subject_prefix = $first['short_name'] . '-' . $last['short_name'];
                    
                    $subject_ids = array_column($group, 'subject_id');
                    $period_ids = array_column($group, 'period_id');
                    $timetable_ids = array_merge(...array_column($group, 'timetable_ids'));
                } else {
                    // Single period
                    $single = reset($group);
                    $subject_prefix = $single['short_name'];
                    
                    $subject_ids = [$single['subject_id']];
                    $period_ids = [$single['period_id']];
                    $timetable_ids = $single['timetable_ids'];
                }
                
                // Final subject text
                $subject = $subject_prefix . ' - ' . $item['display_name'];
                
                $std_sub_map[] = [
                    'subject_id' => implode('###', array_unique($subject_ids)),
                    'period_id'  => implode('###', $period_ids),
                    'subject'    => $subject,
                    'timetable'  => implode('###', $timetable_ids),
                    'extend_lab' => $item['extend_lab'],
                    'type'       => $item['type']
                ];
            }
        }
    }

    return response()->json($std_sub_map);
}

/**
 * Group consecutive periods together
 */
private function groupConsecutivePeriods($periods)
{
    $groups = [];
    $current_group = [];
    
    foreach ($periods as $period) {
        if (empty($current_group)) {
            // Start new group
            $current_group = [$period];
        } else {
            $last_period = end($current_group);
            
            // Check if current period is consecutive to the last one
            if ($period['sort_order'] == $last_period['sort_order'] + 1) {
                // Add to current group
                $current_group[] = $period;
            } else {
                // Save current group and start new one
                $groups[] = $current_group;
                $current_group = [$period];
            }
        }
    }
    
    // Add the last group
    if (!empty($current_group)) {
        $groups[] = $current_group;
    }
    
    return $groups;
}

    public function getBatchTimetable (Request $request)
    {
        // return $request->all();
        $standard_id = $request->standard_id;
        $division_id = $request->division_id;

        if($request->has('type') && $request->type == 'divisionWise') {
        // return $request->all();
        // DB::enableQueryLog();
            $data = DB::table('batch')->where([
                'standard_id' => $standard_id,
                'division_id' => $division_id,
                'syear' => session()->get('syear'),
                'sub_institute_id' =>  session()->get('sub_institute_id'),
            ])->get()->toArray();
            // dd(DB::getQueryLog($data));
            return $data;
        }

        $explode = explode(',', $request->standard_id);

        $arr = $request->server;
        $HTTP_REFERER = "";
        foreach ($arr as $id => $val) {
            if ($id == 'HTTP_REFERER') {
                $HTTP_REFERER = $val;
            }
        }
        $refer_arr = explode('/', $HTTP_REFERER);

        if (count($refer_arr) >= 2 && $refer_arr[count($refer_arr) - 2] == 'exam_creation' || in_array('marks_entry', $refer_arr)) {
            $where = array(
                "sub_std_map.sub_institute_id" => session()->get('sub_institute_id'),
                "sub_std_map.allow_grades" => "Yes",
            );
        } else {
            $where = array(
                "sub_std_map.sub_institute_id" => session()->get('sub_institute_id'),
            );
        }
        if (count($explode) > 1) {
            $std_sub_map = DB::table('subject')
                ->join('sub_std_map', 'subject.id', '=', 'sub_std_map.subject_id')
                ->whereIn("sub_std_map.standard_id", $explode)
                ->where($where)
                ->orderBy('sub_std_map.sort_order')
                ->pluck('sub_std_map.display_name', 'subject.id');
        } else {
            // if (session()->get('user_profile_name') == 'Lecturer') {
            $todayDay = substr(date('l', strtotime($request->date)), 0, 1); // Get first letter of day
            if(strtolower($todayDay) == 't') { // Handle Tuesday/Thursday ambiguity
                $fullDay = strtolower(date('l', strtotime($request->date)));
                $todayDay = ($fullDay == 'thursday') ? 'H' : 'T';
            }
            
            // return strtoupper($todayDay);
            # Get subjects by teacher, standard and division
            $explodeSubject = explode('|||', $request->subject_id);
            $subject = $explodeSubject[0] ?? 0;
            $explodePeriod = explode('###',$explodeSubject[1] ?? '');

            $batch = DB::table('batch as b')
                ->join('timetable as t', 't.batch_id', '=', 'b.id')
                ->when(session()->get('user_profile_name') == 'Lecturer', function ($query) {
                    $query->where('t.teacher_id', session()->get('user_id'));
                })
                ->where('t.standard_id', $request->standard_id)
                ->where('t.division_id', $request->division_id)
                ->where('t.subject_id', $subject)
                ->whereIn('t.period_id', $explodePeriod)
                ->where('t.syear', session()->get('syear'))
                ->where('t.week_day', strtoupper($todayDay))
                ->select(
                    'b.id',
                    'b.title as batch',
                    't.id as timetable_id',
                    't.extend_lab',
                    't.type',
                    't.period_id',
                )
                ->get();
          
                }
        return response()->json($batch);
    }
    
    public function getChapterList(Request $request)
    {
        $explode = explode(',', $request->subject_id);
        $standard_id = $request->standard_id;

        if (count($explode) > 1) {
            $chapter_list = DB::table('chapter_master')
                ->where(['sub_institute_id' => session()->get('sub_institute_id'), "standard_id" => $standard_id])
                ->wherein('subject_id', $explode)
                ->pluck('chapter_name', 'id');
        } else {
            $chapter_list = DB::table('chapter_master')
                ->where([
                    'sub_institute_id' => session()->get('sub_institute_id'),
                    'subject_id' => $request->subject_id,
                    "standard_id" => $standard_id,
                ])
                ->pluck('chapter_name', 'id');
        }

        return response()->json($chapter_list);
    }

    public function getTopicList(Request $request)
    {
        $explode = explode(',', $request->chapter_id);

        if (count($explode) > 1) {
            $topic_list = DB::table('topic_master')
                ->where(['sub_institute_id' => session()->get('sub_institute_id')])
                ->wherein('chapter_id', $explode)
                ->pluck('name', 'id');
        } else {
            $topic_list = DB::table('topic_master')
                ->where([
                    'sub_institute_id' => session()->get('sub_institute_id'),
                    'chapter_id' => $request->chapter_id,
                ])
                ->pluck('name', 'id');
        }

        return response()->json($topic_list);
    }

    public function getExamList(Request $request)
    {
        $where = array(
            "re.sub_institute_id" => session()->get('sub_institute_id'),
            "re.syear" => session()->get('syear'),
            "re.term_id" => $request->term_id,
            "re.standard_id" => $request->standard_id,
            "re.subject_id" => $request->subject_id,
        );
        if (isset($request->exam_id) && $request->exam_id != '') {
            $where = [
                "re.sub_institute_id" => session()->get('sub_institute_id'),
                "re.syear" => session()->get('syear'),
                "re.standard_id" => $request->standard_id,
                "re.exam_id" => $request->exam_id
            ];
            $group = "re.title";
        }

        if ($request->has('searchType') && $request->searchType == 'co') {
            $where['re.subject_id'] = $request->subject_id;
            return DB::table('result_create_exam as re')
                ->join('lo_category as lc', 'lc.id', '=', 're.co_id')
                ->where($where)
                ->pluck('re.title', 're.id');
        }

        $std_sub_map = DB::table('result_create_exam as re')
            ->where($where);
        if (isset($group)) {
            $std_sub_map->groupBy($group);
        }
        $std_sub_map->where('re.exam_id', $request->exam_id);
        $std_sub_map = $std_sub_map->pluck('re.title', 're.id');

        return response()->json($std_sub_map);
    }

    public function getExamsMasterList(Request $request)
    {
        $where = array(
            "re.SubInstituteId" => session()->get('sub_institute_id'),
            "re.term_id" => $request->term_id,
            "re.standard_id" => $request->standard_id,
        );

        $std_sub_map = DB::table('result_exam_master as re')
            ->where($where)
            ->pluck('re.ExamTitle', 're.id');

        return response()->json($std_sub_map);
    }


    public function getCoScholasticParentList(Request $request)
    {
        $where = array(
            "re.sub_institute_id" => session()->get('sub_institute_id'),
        );

        $co_scholastic_parent = DB::table('result_co_scholastic_parent as re')
            ->where($where)
            ->pluck('re.title', 're.id');

        return response()->json($co_scholastic_parent);
    }

    public function getCoScholasticList(Request $request)
    {
        $where = array(
            "re.sub_institute_id" => session()->get('sub_institute_id'),
            "re.parent_id" => $request->co_scholastic_parent_id,
            "re.term_id" => $request->term_id,
        );

        $co_scholastic_parent = DB::table('result_co_scholastic as re')
            ->where($where)
            ->where('re.standard_id', $request->standard_id)
            ->pluck('re.title', 're.id');

        return response()->json($co_scholastic_parent);
    }

    public function getBusList(Request $request)
    {
        $where = array(
            "tv.sub_institute_id" => session()->get('sub_institute_id'),
            "tv.school_shift" => $request->shift_id,
        );

        $bus = DB::table('transport_vehicle as tv')
            ->where($where)
            ->pluck('tv.title', 'tv.id');

        return response()->json($bus);
    }

    public function getStopList(Request $request)
    {

        $school_shift = $request->shift_id;
        $vehicle_id = $request->bus_id;

        $where = array(
            "ss.id" => $school_shift,
            "tv.id" => $vehicle_id,
        );

        $bus = DB::table('transport_stop as ts')
            ->join('transport_route_stop as rs', 'rs.stop_id', '=', 'ts.id')
            ->join('transport_route as tr', 'tr.id', '=', 'rs.route_id')
            ->join('transport_route_bus as rb', 'rb.route_id', '=', 'tr.id')
            ->join('transport_vehicle as tv', 'tv.id', '=', 'rb.bus_id')
            ->join('transport_school_shift as ss', 'ss.id', '=', 'tv.school_shift')
            ->where($where)
            ->groupBy('ts.id')
            ->pluck('ts.stop_name', 'ts.id');

        return response()->json($bus);
    }

    public function getFees(Request $request)
    {
        $months = $request->checkedMonths;
        $student_id = $request->student_id;
        $last_syear = (session()->get('syear') - 1);
        $marking_period_id = session()->get('term_id');
        //ADDED BY RAJESH 12-06-2025
        $termId = session()->get('term_id');

        $last_marking_period_id = ($termId == 1) ? 2 : (($termId == 2) ? 1 : $termId);
        //END

        if (empty($months)) {
            return "";
            exit;
        }


        $stu_arr = array(
            "0" => $student_id,
        );

        $year_arr2 = FeeMonthId($last_syear); //for current year

        $currunt_month = date('m');
        $last_y_month_id = $currunt_month . (session()->get('syear') - 1);
        $search_ids2 = [];
        foreach ($year_arr2 as $id => $arr) {
            if ($id == $last_y_month_id) {
                $search_ids2[] = $id;
                // break;
            } else {
                $search_ids2[] = $id;
            }
        }

        $search_ids = $months;
        $reg_bk_off = FeeBreackoff($stu_arr); // for current year
        $other_bk_off = OtherBreackOff($stu_arr, $search_ids); // for current year
        $other_bk_off_month_wise = OtherBreackOfMonth($stu_arr); // for current year
        $year_arr = FeeMonthId(); // for current year
        $head_wise_fees = FeeBreakoffHeadWise($stu_arr); //for current year
        //   if($student_id==95642){
        //             echo "<pre>";print_r($head_wise_fees);exit;
        //         }
        //echo $last_syear."#".$last_marking_period_id;die();
        $other_bk_off_month_wise2 = $reg_bk_off2 = $other_bk_off2 = $head_wise_fees2 = array();
        if (session()->get('sub_institute_id') != 48 && session()->get('sub_institute_id') != 61) {
            $other_bk_off_month_wise2 = OtherBreackOfMonth($stu_arr, $last_syear); //for previous year
            $reg_bk_off2 = FeeBreackoff($stu_arr, '', $last_syear, $last_marking_period_id); //for previous year
            $other_bk_off2 = OtherBreackOff($stu_arr, $search_ids2, '', '', '', $last_syear); //for previous year
            $head_wise_fees2 = FeeBreakoffHeadWise($stu_arr, '', '', '', $last_syear, '', $last_marking_period_id); //for previous year
        }

        $till_now_breckoff = $till_now_breckoff2 = array();
        foreach ($search_ids as $id => $val) {
            foreach ($head_wise_fees as $temp_id => $arr) {
                foreach ($head_wise_fees[$temp_id]['breakoff'] as $month_id => $fees_detail) {
                    if ($month_id == $val) {
                        $till_now_breckoff[$month_id] = $fees_detail;
                    }
                }
            }
        }
        foreach ($search_ids2 as $id => $val) {
            foreach ($head_wise_fees2 as $temp_id => $arr) {
                foreach ($head_wise_fees2[$temp_id]['breakoff'] as $month_id => $fees_detail) {
                    if ($month_id == $val) {
                        $till_now_breckoff2[$month_id] = $fees_detail;
                    }
                }
            }
        }

        $reg_bk_month_wise = array();
        $reg_bk_month_wise2 = array();

        $final_bk_name = array();
        $total = 0;

        foreach ($till_now_breckoff as $month_id => $fees_detail) {
            foreach ($fees_detail as $head_name => $arr) {
                if ($arr['amount'] !== 0) {
                    if (!isset($reg_bk_month_wise[$arr['title']])) {
                        $reg_bk_month_wise[$arr['title']] = 0;
                    }
                    // 03-06-24 by uma for institute_id =248 // commented on 2024-07-30
                    // if(isset($arr['disc_amount']) && $arr['disc_amount']>0 && $arr['amount']>=$arr['disc_amount']){
                    //     $reg_bk_month_wise[$arr['title']] += ($arr['amount']-$arr['disc_amount']); 
                    // }else{
                    $reg_bk_month_wise[$arr['title']] += ($arr['amount']);
                    // }
                    $final_bk_name[$arr['title']] = $head_name;
                }
            }
        }

        // return $final_bk_name;exit;
        foreach ($till_now_breckoff2 as $month_id => $fees_detail) {
            foreach ($fees_detail as $head_name => $arr) {
                if (!isset($reg_bk_month_wise2[$arr['title']])) {
                    $reg_bk_month_wise2[$arr['title']] = 0;
                }
                // commented on 2024-07-30
                // if(isset($arr['disc_amount']) && $arr['disc_amount']>0 && $arr['amount']>=$arr['disc_amount']){
                //     $reg_bk_month_wise2[$arr['title']] += ($arr['amount']-$arr['disc_amount']); 
                // }else{
                $reg_bk_month_wise2[$arr['title']] += ($arr['amount']);
                // }
                // $reg_bk_month_wise2[$arr['title']] += $arr['amount'];
                $final_bk_name[$arr['title']] = $head_name;
            }
        }

        // echo "<pre>";print_r($other_bk_off);exit;

        $full_bk = array_merge($reg_bk_month_wise, $other_bk_off);

        $full_bk2 = array_merge($reg_bk_month_wise2, $other_bk_off2);

        $feeTitles = array_keys($full_bk);
        $feeTitlesIn = implode("','", $feeTitles);

        $sortOrders = DB::table('fees_title')
            ->whereRaw("display_name IN ('" . $feeTitlesIn . "')")
            ->where(['sub_institute_id' => session()->get('sub_institute_id'), 'syear' => session()->get('syear')])
            ->orderBy('sort_order')
            ->pluck('sort_order', 'display_name');

        uksort($full_bk, function ($a, $b) use ($sortOrders) {
            return $sortOrders[$a] <=> $sortOrders[$b];
        });


        $previous = array_sum($full_bk2);

        if ($previous > 0 && !in_array(session()->get('sub_institute_id'), [133])) {
            // if ($previous > 0) {            
            $full_bk['Previous Fees'] = $previous;
            $final_bk_name["Previous Fees"] = "previous_fees";
        }
        // echo "<pre>";print_r($full_bk);exit;
        foreach ($full_bk as $id => $val) {
            $total = $total + $val;
        }

        $other_fee_title = OtherBreackOffHead(); //for current year

        foreach ($other_fee_title as $id => $arr) {
            foreach ($full_bk as $title => $val) {
                if ($title == $arr->display_name) {
                    $final_bk_name[$title] = $arr->other_fee_id;
                }
            }
        }

        $full_bk["Total"] = $total;
        // fees collect table for collecting amount
        $response = "";
        $response .= ' <tr class="spaceUnder">
                        <th  align="center" style="width: 30%;align-content: center;">Particular</th>
                        <th style="width: 10%;padding-left: 15px;">Amount</th>
                        <th style="width: 20%;padding-left: 15px;">Collection Amount</th>
                        <th style="width: 20%;padding-left: 15px;display:none">' . get_string('Discount', 'requests') . '</th>
                        <th style="width: 20%;padding-left: 15px;display:none">Fine</th>
                    </tr>'; // hide discount and fine columns 04-02-2025
        foreach ($full_bk as $id => $val) {
            if ($val != 0) {
                $ids = '';
                if ($id == "Total") {
                    $ids = 'id="all_total"';
                }
                $response .= "
                 <tr>
                    <td style='width: 20%'>$id</td>
                    <td style='width: 20%' $ids>$val</td>
            ";
                if ($id != 'Total') {
                    // $response .= "<td style='width: 20%'><input type='number' min=0 max=$val  value='" . $val . "' name='fees_data[" . $final_bk_name[$id] . "]' class='form-control allField1'></td>";
                    $response .= "<td style='width: 20%'><input type='number' min='0' max='$val' value='$val' name='fees_data[" . $final_bk_name[$id] . "]' class='form-control allField1' id=" . $final_bk_name[$id] . "></td>";

                    $response .= "<input type='hidden' value='" . $val . "' name='hid_fees_data[" . $final_bk_name[$id] . "]' class='hid_allField1'>";
                    $response .= "<td style='width: 20%;display:none'><input type='number' value='0' name='discount_data[" . $final_bk_name[$id] . "]' class='form-control allDisField' style='min-width:150px;'></td>"; // min=0 max=$val
                    $response .= "<td style='width: 20%;display:none'><input type='number'  min=0 value=0 name='fine_data[" . $final_bk_name[$id] . "]' class='form-control allFinField' style='min-width:150px;'></td>";
                } else {
                    $response .= "<td style='width: 25%'><input type='text' id='totalVal' name='total' value='" . $total . "' class='form-control'></td>";
                    $response .= "<td style='width: 25%;display:none'><input type='text'  value='0' class='form-control directdiscount'></td>"; // id='totalDis' name='totalDis'
                    $response .= "<td style='width: 25%;display:none'><input id='totalFin' type='text' name='totalFin' value='0' class='form-control directfine'></td>";
                }
                $response .= "</tr>"; // hide discount and fine columns 04-02-2025
            }
        }

        return $response;
    }

    public function getOnlineFees(Request $request)
    {

        $months = $request->checkedMonths;
        $student_id = $request->student_id;
        $fees_type = $request->fees_type;

        if (empty($months)) {
            return "";
            exit;
        }

        $stu_arr = array(
            "0" => $student_id,
        );
        $search_ids = $months;
        $reg_bk_off = FeeBreackoff($stu_arr);
        $other_bk_off = OtherBreackOff($stu_arr, $search_ids);
        $other_bk_off_month_wise = OtherBreackOfMonth($stu_arr);
        $year_arr = FeeMonthId();

        $head_wise_fees = FeeBreakoffHeadWise($stu_arr);
        $till_now_breckoff = array();
        foreach ($search_ids as $id => $val) {
            foreach ($head_wise_fees as $temp_id => $arr) {
                foreach ($head_wise_fees[$temp_id]['breakoff'] as $month_id => $fees_detail) {
                    if ($month_id == $val) {
                        $till_now_breckoff[$month_id] = $fees_detail;
                    }
                }
            }
        }

        $reg_bk_month_wise = array();
        $final_bk_name = array();
        $total = 0;

        foreach ($till_now_breckoff as $month_id => $fees_detail) {
            foreach ($fees_detail as $head_name => $arr) {
                if (!isset($reg_bk_month_wise[$arr['title']])) {
                    $reg_bk_month_wise[$arr['title']] = 0;
                }
                $reg_bk_month_wise[$arr['title']] += $arr['amount'];
                $final_bk_name[$arr['title']] = $head_name;
            }
        }

        $full_bk = array_merge($reg_bk_month_wise, $other_bk_off);

        foreach ($full_bk as $id => $val) {
            $total = $total + $val;
        }

        $other_fee_title = OtherBreackOffHead();

        foreach ($other_fee_title as $id => $arr) {
            foreach ($full_bk as $title => $val) {
                if ($title == $arr->display_name) {
                    $final_bk_name[$title] = $arr->other_fee_id;
                }
            }
        }

        $full_bk["Total"] = $total;

        $response = "";
        $response .= ' <tr class="spaceUnder">
                        <th  align="center" style="width: 30%;align-content: center;">Particular</th>
                        <th style="width: 10%;padding-left: 15px;">Amount</th>
                    </tr>';
        foreach ($full_bk as $id => $val) {
            $response .= "
                 <tr>

                    <td style='width: 20%'>$id</td>
                    <td style='width: 20%'>$val</td>
            ";
            if ($id == 'Total') {
                $response .= "<input type='hidden' id='totalVal' name='total' value='" . $total . "' class='form-control'>";
            }

            $response .= "</tr>";
        }

        return $response;
    }

    public function getOnlineFeesMonth($arr)
    {

        $months = $arr["months"];
        $student_id = $arr["student_id"];


        if (empty($months)) {
            return "";
            exit;
        }

        $stu_arr = array(
            "0" => $student_id,
        );
        $search_ids = $months;
        $reg_bk_off = FeeBreackoff($stu_arr);
        $other_bk_off = OtherBreackOff($stu_arr, $search_ids);
        $other_bk_off_month_wise = OtherBreackOfMonth($stu_arr);
        $year_arr = FeeMonthId();

        $head_wise_fees = FeeBreakoffHeadWise($stu_arr);
        $till_now_breckoff = array();
        foreach ($search_ids as $id => $val) {
            foreach ($head_wise_fees as $temp_id => $arr) {
                foreach ($head_wise_fees[$temp_id]['breakoff'] as $month_id => $fees_detail) {
                    if ($month_id == $val) {
                        $till_now_breckoff[$month_id] = $fees_detail;
                    }
                }
            }
        }

        $reg_bk_month_wise = array();
        $final_bk_name = array();
        $total = 0;

        foreach ($till_now_breckoff as $month_id => $fees_detail) {
            foreach ($fees_detail as $head_name => $arr) {
                if (!isset($reg_bk_month_wise[$arr['title']])) {
                    $reg_bk_month_wise[$arr['title']] = 0;
                }
                $reg_bk_month_wise[$arr['title']] += $arr['amount'];
                $final_bk_name[$arr['title']] = $head_name;
            }
        }

        $full_bk = array_merge($reg_bk_month_wise, $other_bk_off);

        foreach ($full_bk as $id => $val) {
            $total = $total + $val;
        }

        $other_fee_title = OtherBreackOffHead();

        foreach ($other_fee_title as $id => $arr) {
            foreach ($full_bk as $title => $val) {
                if ($title == $arr->display_name) {
                    $final_bk_name[$title] = $arr->other_fee_id;
                }
            }
        }

        $full_bk["Total"] = $total;

        return $full_bk;
    }

    public function getLOSubjectList(Request $request)
    {
        $standard = $request->standard_id;
        $medium = $request->medium_id;

        $where = array(
            'learning_outcome_pdf.standard' => $standard,
            'learning_outcome_pdf.medium' => $medium,
        );

        $std_sub_map = DB::table('learning_outcome_pdf')
            ->where($where)
            ->pluck('learning_outcome_pdf.DISPLAY_SUBJECT', 'learning_outcome_pdf.SUBJECTS');

        return response()->json($std_sub_map);
    }

    public function getLOList(Request $request)
    {
        $standard = $request->standard_id;
        $medium = $request->medium_id;
        $subject = $request->subject_id;

        $where = array(
            'learning_outcome_indicator.standard' => $standard,
            'learning_outcome_indicator.medium' => $medium,
            'learning_outcome_indicator.subject' => $subject,
        );

        $std_sub_map = DB::table('learning_outcome_indicator')
            ->where($where)
            ->pluck('learning_outcome_indicator.INDICATOR', 'learning_outcome_indicator.ID');

        return response()->json($std_sub_map);
    }

    public function getSubModuleList(Request $request)
    {
        $main_module_name = DB::table("report_module")
            ->where("id", $request->main_module_id)
            ->get();


        $all_sub_module = DB::table("report_module")
            ->where("main_module", $main_module_name[0]->main_module)
            ->pluck("sub_module", "id");

        foreach ($all_sub_module as $id => $val) {
            if ($val == "") {
                unset($all_sub_module[$id]);
            }
        }

        return response()->json($all_sub_module);
    }

    public function getStudentFromMobile(Request $request)
    {
        $mobile = $_REQUEST["mobile_number"];

        $all_student = DB::table("tblstudent as s")
            ->join('fees_online_maping as fo', 'fo.sub_institute_id', '=', 's.sub_institute_id')
            ->join('school_setup as ss', 'ss.id', '=', 'fo.sub_institute_id')
            ->select(
                DB::raw("CONCAT(s.first_name,' ',s.last_name,' - ',ss.shortcode) AS name"),
                's.id',
                'fo.bank_name',
                's.sub_institute_id'
            )
            ->where(['s.mobile' => $mobile]) //,'fo.sub_institute_id' => $sub_institute_id
            ->get();

        return response()->json($all_student);
    }

    public function ajax_checkFeesBreakoff(Request $request)
    {
        $student_id = $_REQUEST["student_id"];

        $get_enrollment_data = DB::table('tblstudent_enrollment')->where('student_id', $student_id)
            ->orderBy('syear', 'DESC')->limit(1)->get()->toArray();

        $get_enrollment_data = $get_enrollment_data[0];

        $bf_data = DB::table('tblstudent as s')
            ->leftJoin('fees_breackoff as fb', function ($join) use ($get_enrollment_data) {
                $join->whereRaw("fb.sub_institute_id = s.sub_institute_id AND fb.admission_year = s.admission_year AND fb.quota = '"
                    . $get_enrollment_data->student_quota . "' AND fb.grade_id = '" . $get_enrollment_data->grade_id . "' AND fb.syear = '"
                    . $get_enrollment_data->syear . "' AND fb.standard_id = '" . $get_enrollment_data->standard_id . "'");
            })
            ->selectRaw('SUM(IFNULL(fb.amount,0)) AS total_amount')
            ->where('s.id', $student_id)->get()->toArray();

        $medium_data = DB::table('academic_section')->where('id', $get_enrollment_data->grade_id)
            ->select('medium')->get()->toArray();

        return $bf_data[0]->total_amount . '####' . $medium_data[0]->medium;
    }

    public function ajax_load_rightSideMenu(Request $request)
    {
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $user_id = $request->session()->get('user_id');
        $main_menu_id = $request->menu_id;

        $RightSideMenu = $RS_Menu = $RS_ChildMenu = array();

        $RSMainQuery = DB::table('rightside_menumaster as m')
            ->whereRaw("FIND_IN_SET('" . $sub_institute_id . "', m.sub_institute_id) AND
                m.parent_menu_id = 0 AND main_menu_id = '" . $main_menu_id . "' AND m.status=1")
            ->orderBy('sort_order')->get()->toArray();

        $RSMainQuery = json_decode(json_encode($RSMainQuery), true);

        if (count($RSMainQuery) > 0) {
            foreach ($RSMainQuery as $key => $value) {
                $RS_Menu[$value['id']] = $value;
            }
        }

        $RSChildQuery = DB::table('tbluser as u')
            ->leftJoin('tblindividual_rights as i', function ($join) {
                $join->whereRaw('u.id = i.user_id AND u.sub_institute_id = i.sub_institute_id');
            })->leftJoin('tblgroupwise_rights as g', function ($join) {
                $join->whereRaw('u.user_profile_id = g.profile_id AND u.sub_institute_id = g.sub_institute_id');
            })->join('rightside_menumaster as m', function ($join) {
                $join->whereRaw('(i.menu_id = m.tblmenu_master_id OR g.menu_id = m.tblmenu_master_id) AND m.status=1');
            })->join('tblmenumaster as mm', function ($join) use ($sub_institute_id) {
                $join->whereRaw("mm.id = m.tblmenu_master_id AND FIND_IN_SET('" . $sub_institute_id . "', m.sub_institute_id) AND mm.status=1");
            })
            ->selectRaw('distinct(m.id),m.*,mm.link')
            ->where('u.sub_institute_id', $sub_institute_id)
            ->where('u.id', $user_id)
            ->where('m.main_menu_id', $main_menu_id)->get()->toArray();

        $RSChildQuery = json_decode(json_encode($RSChildQuery), true);

        if (count($RSChildQuery) > 0) {
            foreach ($RSChildQuery as $key1 => $value1) {
                $RS_ChildMenu[$value1['parent_menu_id']][] = $value1;
            }
        }

        $i = 1;
        $main_menu = $child_menu = "";

        foreach ($RS_Menu as $key => $val) {
            if (isset($RS_ChildMenu[$val['id']])) {
                if ($i == 1) {
                    $active = "active";
                } else {
                    $active = "";
                }
                $main_menu .= '<li class="nav-item" role="presentation" data-toggle="tooltip" data-placement="top"
                title="' . $val['name'] . '"><a class="nav-link ' . $active . '" data-toggle="tab" href="#right-tab-' . $i . '"
                role="tab" aria-controls="right-tab-' . $i . '" aria-selected="false"><img class="icon-nrml"
                src="' . env('APP_URL') . '/admin_dep/images/side-' . $val['icon'] . '.png" alt="">
                <img class="icon-hvr" src="' . env('APP_URL') . '/admin_dep/images/side-' . $val['icon'] . '-white.png"
                alt=""></a></li>';

                $child_arr = $RS_ChildMenu[$val['id']];
                $child_li = "";
                foreach ($child_arr as $ckey => $cval) {
                    $child_li .= '<li class="d-flex align-items-center"><i class="fa fa-angle-right" style="margin-right: 8px;">
                    </i><a href="' . route($cval['link']) . '" onclick="sessionMenu(' . $cval['tblmenu_master_id'] . ');" >' . $cval['name'] . '</a></li>';
                    if ($cval['name'] == 'Field Settings') {
                        $export_import_link = "window.open('" . env('APP_URL') . "excel_upload/export_xlsx.php?sub_institute_iderp=" . $sub_institute_id . "','scrollbars=yes,resizable=no,status=no,location=no,toolbar=no,menubar=no','width=600,height=300,left=100,top=100')";
                        $child_li .= '<li><i class="fa fa-angle-right" style="margin-right: 8px;">
                        </i><a href="javascript:void(0);" onclick="' . $export_import_link . '" class="waves-effect">Excel Import/Export</a></li>';
                        $child_li .= '<li><i class="fa fa-angle-right" style="margin-right: 8px;">
                        </i><a href="' . route('import.data') . '">Import Data</a></li>';
                        $child_li .= '<li><i class="fa fa-angle-right" style="margin-right: 8px;">
                        </i><a href="' . route('workflow.index') . '">Workflow</a></li>';
                    }
                }
                $child_menu .= '<div class="tab-pane show ' . $active . '" id="right-tab-' . $i . '"
                role="tabpanel"><div class="acc-panel"><div class="acc-header d-flex align-items-center">
                <span><i class="fa fa-angle-down" style="margin-right: 8px;"></i></span><h4 class="m-0">' . $val['name'] . '</h4>
                </div><div class="acc-body" style="display: block;"><ul class="list-unstyled activity-checks">' . $child_li . '</ul>
                <div class="activity-accordian"></div></div></div></div>';
                $i++;
            }
        }

        return $main_menu . "####" . $child_menu;
    }

    public function ajax_load_helpguide(Request $request)
    {

        $sub_institute_id = $request->session()->get('sub_institute_id');
        $user_id = $request->session()->get('user_id');
        $main_menu_id = $request->menu_id;

        $helpguide = array();

        $data = DB::table('tblmenumaster')->where('id', $main_menu_id)->get()->toArray();

        if (!empty($data)) {
            $data = json_decode(json_encode($data), true);
            $data = $data[0];

            if ($data['youtube_link'] != "") {
                return $data['youtube_link'] . "####" . $data['pdf_link'];
            } else {
                return "0";
            }
        } else {
            return "0";
        }
    }

    public function ajax_sendmail(Request $request)
    {
        // require_once('mailer/class.phpmailer.php');
        $sub_institute_id = $request->session()->get('sub_institute_id');

        $mail = DB::table('smtp_details')->where('sub_institute_id', $sub_institute_id)->get()->toArray();
        $mail = json_decode(json_encode($mail), true);
        $smtp_details = $mail[0];

        if (count($mail) > 0) {
            $from = $smtp_details['gmail'];
            $from_pass = $smtp_details['password'];
            $subject = $request->get('subject');
            $send_to = $request->get('email');
            $message = $request->get('message');


            $mail = new PHPMailer\PHPMailer();
            $mail->IsSMTP();
            $mail->isHTML(true);
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "tls";
            $mail->Host = $smtp_details['server_address'];
            $mail->Port = $smtp_details['port'];

            $mail->AddAddress($send_to);
            $mail->Username = $from;
            $mail->Password = $from_pass;
            $mail->SetFrom($from, $from);
            $mail->AddReplyTo($from, $from);

            if (!empty($request->get('attachment'))) {
                $attachment = $request->get('attachment');
                $mail->addAttachment($attachment);
            }

            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = $message;
            $mail->Send();
        }
        // return $request;
        return redirect()->back();
    }

    public function ajax_sendEmailFeesReceipt(Request $request)
    {
        //    return $this->ajax_sendmail($request);exit;
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $student_id = $request->input('student_id');
        $receipt_id = $request->input('receipt_id_html');
        $action = $request->input('action');

        $fees_receipt_html = $this->get_FeesHtml($student_id, $action, $receipt_id);
        $fees_css = $this->get_FeesCss($action);
        $fees_receipt_css = "<style>" . $fees_css . "</style>";

        if ($fees_receipt_html != '') {
            $dom = '<!DOCTYPE html>
                    <html>
                        <head>
                           <title></title>
                           <meta charset="UTF-8">
                           <meta name="viewport" content="width=erpice-width, initial-scale=1.0">';
            $dom .= $fees_receipt_css;
            $dom .= '</head>
                        <body>
                            <div>
                                ##HTML_SEC##
                            </div>
                        </body>
                    </html>';

            $getEmailAddress = DB::table('tblstudent')->selectRaw('id,email,enrollment_no,mobile')
                ->where('sub_institute_id', $sub_institute_id)
                ->where('id', $student_id)->get()->toArray();

            $getEmails = json_decode(json_encode($getEmailAddress), true);
            $getEmails = $getEmails[0];

            $to_arr = $getEmails['email'];

            $save_path = $_SERVER['DOCUMENT_ROOT'] . '/storage/mail_receipt_pdf';

            $CUR_TIME = date('YmdHis');
            $html_filename = $student_id . '_' . $CUR_TIME . ".html";
            $pdf_filename = $student_id . '_' . $CUR_TIME . ".pdf";

            $html = '';
            $html .= $fees_receipt_html;
            $path = 'src="https://' . $_SERVER['HTTP_HOST'];
            $html = str_replace('src="', $path, $html);
            $html = str_replace('##HTML_SEC##', $html, $dom);

            $html_file_path = $save_path . '/' . $html_filename;
            $pdf_file_path = $save_path . '/' . $pdf_filename;
            file_put_contents($html_file_path, $html);
            htmlToPDF($html_file_path, $pdf_file_path);

            if ($action == 'imprest_ledger_view') {
                $EMAIL_TEXT = "Dear Parents,
                     <br/>
                     <br/>
                        Kindly see the attachment for Imprest Ledger.
                     <br/>
                     <br/>
                     Regards,
                     <br/>
                    ";
            } else {
                $EMAIL_TEXT = "Dear Parents,
                     <br/>
                     <br/>
                        Kindly see the attachment for Fees Receipt.
                     <br/>
                     <br/>
                     Regards,
                     <br/>
                    ";
            }

            if ($action == 'imprest_ledger_view') {
                $request->request->add(['subject' => 'Imprest Ledger Sheet']);
            } else {
                $request->request->add(['subject' => 'Fees Receipt']);
            }
            $request->request->add(['email' => $to_arr]);
            $request->request->add(['message' => $EMAIL_TEXT]);
            $request->request->add(['attachment' => $pdf_file_path]);

            $this->ajax_sendmail($request);
            unlink($html_file_path);
            unlink($pdf_file_path);

            if ($action == 'imprest_ledger_view') {
                return '2';
            } else {
                return '1';
            }
        }
    }

    public function ajax_sendBulkEmailFeesReceipt(Request $request)
    {
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $last_inserted_ids = $request->input('inserted_ids');
        $action = $request->input('action');

        $inserted_ids_arr = explode(',', $last_inserted_ids);

        foreach ($inserted_ids_arr as $key => $value) {

            $html_data = $this->get_FeesHtmlForBulk($action, $value);
            $student_id = $html_data['student_id'];
            $fees_receipt_html = $html_data['fees_receipt_html'];

            if ($fees_receipt_html != '') {
                $dom = '<!DOCTYPE html>
                        <html>
                            <head>
                               <title></title>
                               <meta charset="UTF-8">
                               <meta name="viewport" content="width=erpice-width, initial-scale=1.0">
                            </head>
                            <body>
                                <div>
                                    ##HTML_SEC##
                                </div>
                            </body>
                        </html>';

                $getEmailAddress = DB::table('tblstudent')->selectRaw('id,email,enrollment_no,mobile')
                    ->where('sub_institute_id', $sub_institute_id)
                    ->where('id', $student_id)->get()->toArray();

                $getEmails = json_decode(json_encode($getEmailAddress), true);
                $getEmails = $getEmails[0];

                $to_arr = $getEmails['email'];

                $save_path = $_SERVER['DOCUMENT_ROOT'] . '/storage/mail_receipt_pdf';

                $CUR_TIME = date('YmdHis');
                $html_filename = $student_id . '_' . $CUR_TIME . ".html";
                $pdf_filename = $student_id . '_' . $CUR_TIME . ".pdf";

                $html = '';
                $html .= $fees_receipt_html;
                $path = 'src="https://' . $_SERVER['HTTP_HOST'];
                $html = str_replace('src="', $path, $html);
                $html = str_replace('##HTML_SEC##', $html, $dom);

                $html_file_path = $save_path . '/' . $html_filename;
                $pdf_file_path = $save_path . '/' . $pdf_filename;
                file_put_contents($html_file_path, $html);
                if ($action == 'fees_circular') {
                    htmlToPDFLandscape($html_file_path, $pdf_file_path);
                } else {
                    htmlToPDF($html_file_path, $pdf_file_path);
                }

                if ($action == 'fees_circular') {
                    $EMAIL_TEXT = "Dear Parents,
                         <br/>
                         <br/>
                            Kindly see the attachment for Fees Circular.
                         <br/>
                         <br/>
                         Regards,
                         <br/>
                         <br/>
                         TMS-Surat
                        ";
                } else {
                    $EMAIL_TEXT = "Dear Parents,
                         <br/>
                         <br/>
                            Kindly see the attachment for Other Fees Receipt.
                         <br/>
                         <br/>
                         Regards,
                         <br/>
                         <br/>
                         TMS-Surat
                        ";
                }

                if ($action == 'fees_circular') {
                    $request->request->add(['subject' => 'Fees Circular']);
                } else {
                    $request->request->add(['subject' => 'Other Fees Receipt']);
                }
                $request->request->add(['email' => $to_arr]);
                $request->request->add(['message' => $EMAIL_TEXT]);
                $request->request->add(['attachment' => $pdf_file_path]);

                $this->ajax_sendmail($request);
                unlink($html_file_path);
                unlink($pdf_file_path);
            }
        }

        if ($action == 'fees_circular') {

            return '2';
        } else {

            return '1';
        }
    }

    public function ajax_PDF_FeesReceipt(Request $request)
    {
        //Start For Empty folder before creating new PDF
        $folder_path = $_SERVER['DOCUMENT_ROOT'] . '/storage/print_receipt_pdf/*';
        $files = glob($folder_path); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
        //END For Empty folder before creating new PDF

        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $student_id = $request->input('student_id');
        $receipt_id = $request->input('receipt_id_html');
        $action = $request->input('action');
        $paper_size = $request->input('paper_size');


        $fees_receipt_html = $this->get_FeesHtml($student_id, $action, $receipt_id);
        $fees_css = $this->get_FeesCss($action);
        $fees_receipt_css = "<style>" . $fees_css . "</style>";

        if ($fees_receipt_html != '') {
            $dom = '<!DOCTYPE html>
                    <html>
                        <head>
                           <title></title>
                           <meta charset="UTF-8">
                           <meta name="viewport" content="width=erpice-width, initial-scale=1.0">
                           <style>@font-face {font-family: "Aakar";src: url("' . asset("fonts/Aakar.ttf") . '") format("truetype");}
                            body {font-family: "Aakar", sans-serif; // Use the defined font-family}</style>';
            $dom .= $fees_receipt_css;
            $dom .= '</head>
                        <body>';
            $dom .= $this->get_PageSetup($paper_size);

            $dom .= '</body>
                </html>';

            $save_path = $_SERVER['DOCUMENT_ROOT'] . '/storage/print_receipt_pdf';

            $CUR_TIME = date('YmdHis');
            $html_filename = $student_id . '_' . $CUR_TIME . ".html";
            $pdf_filename = $student_id . '_' . $CUR_TIME . ".pdf";

            $html = '';
            if (is_array($fees_receipt_html) == 1) {
                foreach ($fees_receipt_html as $key => $val) {
                    $html .= $val['fees_html'];
                }
            } else {
                $html .= $fees_receipt_html;
            }

            if ($action != 'certificate_re_receipt') {
                //Hide By Rajesh display Logo proper but in PDF not see = 23-07-2024
                //$path = 'src="https://' . $_SERVER['HTTP_HOST'];
                //$html = str_replace('src="', $path, $html);
            }

            $html = str_replace('##HTML_SEC##', $html, $dom);

            $html_file_path = $save_path . '/' . $html_filename;
            $pdf_file_path = $save_path . '/' . $pdf_filename;
            file_put_contents($html_file_path, $html);

            $htmlToPDF = $this->htmlToPDF_making($paper_size, $html_file_path, $pdf_file_path);

            unlink($html_file_path);

            $PDF_path_for_open = "https://" . $_SERVER['HTTP_HOST'] . '/storage/print_receipt_pdf/' . $pdf_filename;

            return $PDF_path_for_open;
        }
    }

    public function ajax_PDF_Bulk_OtherFeesReceipt(Request $request)
    {
        //Start For Empty folder before creating new PDF
        $folder_path = $_SERVER['DOCUMENT_ROOT'] . '/storage/print_receipt_pdf/*';
        $files = glob($folder_path); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
        //END For Empty folder before creating new PDF

        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $last_inserted_ids = $request->input('inserted_ids');
        $action = $request->input('action');
        $paper_size = $request->input('page_size');

        $inserted_ids_arr = explode(',', $last_inserted_ids);

        $html = '';
        foreach ($inserted_ids_arr as $key => $value) {

            $html_data = $this->get_FeesHtmlForBulk($action, $value);
            $student_id = $html_data['student_id'];
            $fees_receipt_html = $html_data['fees_receipt_html'];
            // dd($html_data);

            if ($fees_receipt_html != '') {
                $dom = '<!DOCTYPE html>
                        <html>
                            <head>
                               <title></title>
                               <meta charset="UTF-8">
                               <meta name="viewport" content="width=erpice-width, initial-scale=1.0">                           
                            </head>
                            <style>
                            .fees-receipt tbody tr{
                                 page-break-inside: avoid;
                            }
                            </style>
                            <body>';
                if ($action == 'other_fees_collect_receipt') {
                    $dom .= $this->get_PageSetup($paper_size);
                } else {
                    $dom .= '  <div style="page-break-inside: avoid">
                                ##HTML_SEC##
                            </div>';
                }

                $dom .= '  </body>
                        </html>';

                $save_path = $_SERVER['DOCUMENT_ROOT'] . '/storage/print_receipt_pdf';

                $CUR_TIME = date('YmdHis');
                $html_filename = $student_id . '_' . $CUR_TIME . ".html";
                $pdf_filename = $student_id . '_' . $CUR_TIME . ".pdf";

                // $html = '';
                $html .= $fees_receipt_html; //.'<div class="last_page" style="page-break-before: always !important;"></div>';
                $html = str_replace('##HTML_SEC##', $html, $dom);

                $html_file_path = $save_path . '/' . $html_filename;
                $pdf_file_path = $save_path . '/' . $pdf_filename;
                file_put_contents($html_file_path, $html);

                if ($action == 'Bonafide' || $action == 'Character Certificate' || $action == 'imprest_fees_cancel_refund_receipt') {
                    htmlToPDFLandscapeCertificate($html_file_path, $pdf_file_path);
                } else if ($action == 'other_fees_collect_receipt') {
                    $this->htmlToPDF_making($paper_size, $html_file_path, $pdf_file_path);
                } else {
                    htmlToPDF($html_file_path, $pdf_file_path);
                }

                unlink($html_file_path);

                $PDF_path_for_open = "https://" . $_SERVER['HTTP_HOST'] . '/storage/print_receipt_pdf/' . $pdf_filename;
            }
        }
        return $PDF_path_for_open;
    }

    public function get_FeesHtml($student_id, $action, $receipt_id)
    {
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');

        if ($action == 'imprest_ledger_view') {
            $NewRequest = new Request();
            $NewRequest->request->add(['student_id' => $student_id]);

            $get_controller = new otherNewfeesReportController;
            $fees_receipt_html = $get_controller->ajax_ledgerData($NewRequest);
        } elseif ($action == 'other_fees_re_receipt') {

            $get_data = DB::table('fees_other_collection')
                ->where('sub_institute_id', $sub_institute_id)
                ->where('syear', $syear)
                ->where('receipt_id', $receipt_id)
                ->where('student_id', $student_id)->get()->toArray();

            $other_fees_collection_data = json_decode(json_encode($get_data), true);
            $other_fees_collection_data = $other_fees_collection_data[0];
            $fees_receipt_html = $other_fees_collection_data['paid_fees_html'];
        } elseif ($action == 'certificate_re_receipt') {
            $get_data = DB::table('certificate_history')
                ->where('sub_institute_id', $sub_institute_id)
                ->where('syear', $syear)
                ->where('id', $receipt_id)
                ->where('student_id', $student_id)->get()->toArray();

            $certificate_data = json_decode(json_encode($get_data), true);
            $certificate_data = $certificate_data[0];
            $fees_receipt_html = $certificate_data['certificate_html'];
        } elseif ($action == 'fees_refund_receipt') {
            $fees_refund_data = DB::table('fees_refund')
                ->where('sub_institute_id', $sub_institute_id)
                ->where('syear', $syear)
                ->where('id', $receipt_id)
                ->where('student_id', $student_id)->get()->toArray();

            $fees_refund_data = json_decode(json_encode($fees_refund_data), true);
            $fees_refund_data = $fees_refund_data[0];
            $fees_receipt_html = $fees_refund_data['fees_html'];
        } else {
            // $unionQuery = DB::table('fees_paid_other as fo')
            //     ->join('fees_receipt as fro', function ($join) {
            //         $join->whereRaw('FIND_IN_SET(fo.id,fro.OTHER_FEES_ID) AND fro.SUB_INSTITUTE_ID = fo.sub_institute_id');
            //     })
            //     ->selectRaw('fo.id,fo.student_id,fo.reciept_id AS receipt_no,fo.paid_fees_html AS fees_html')
            //     ->where('fo.sub_institute_id', $sub_institute_id)
            //     ->where('fo.syear', $syear)
            //     ->where('fo.student_id', $student_id)
            //     ->whereRaw("(fro.RECEIPT_ID_1 = '" . $receipt_id . "' OR fro.RECEIPT_ID_2 = '" . $receipt_id . "' OR fro.RECEIPT_ID_3 = '" . $receipt_id . "' OR fro.RECEIPT_ID_4 = '" . $receipt_id . "' OR fro.RECEIPT_ID_5 = '" . $receipt_id . "' OR fro.RECEIPT_ID_6 = '" . $receipt_id . "' OR fro.RECEIPT_ID_7 = '" . $receipt_id . "' OR fro.RECEIPT_ID_8 = '" . $receipt_id . "'
            //         OR fro.RECEIPT_ID_9 = '" . $receipt_id . "' OR fro.RECEIPT_ID_10 = '" . $receipt_id . "')")
            //     ->groupBy('fo.paid_fees_html');

            // $get_data = DB::table('fees_collect as fc')
            //     ->join('fees_receipt as fr', function ($join) {
            //         $join->whereRaw('FIND_IN_SET(fc.id,fr.FEES_ID) AND fr.SUB_INSTITUTE_ID = fc.sub_institute_id');
            //     })
            //     ->selectRaw('fc.id,fc.student_id,fc.receipt_no,fc.fees_html')
            //     ->where('fc.sub_institute_id', $sub_institute_id)
            //     ->where('fc.syear', $syear)
            //     ->where('fc.student_id', $student_id)
            //     ->whereRaw("(fr.RECEIPT_ID_1 = '" . $receipt_id . "' OR fr.RECEIPT_ID_2 = '" . $receipt_id . "' OR fr.RECEIPT_ID_3 = '" . $receipt_id . "'
            //         OR fr.RECEIPT_ID_4 = '" . $receipt_id . "' OR fr.RECEIPT_ID_5 = '" . $receipt_id . "' OR fr.RECEIPT_ID_6 = '" . $receipt_id . "' OR fr.RECEIPT_ID_7 = '" . $receipt_id . "' OR fr.RECEIPT_ID_8 = '" . $receipt_id . "'
            //         OR fr.RECEIPT_ID_9 = '" . $receipt_id . "' OR fr.RECEIPT_ID_10 = '" . $receipt_id . "')")
            //     ->groupBy('fc.fees_html')
            //     ->union($unionQuery)->groupBy('fees_html')->get()->toArray();

            // 04-10-23 by uma lions double fees_recipt
            $get_data = DB::table(function ($query) use ($sub_institute_id, $syear, $student_id, $receipt_id) {
                $query->selectRaw('fc.id, fc.student_id, fc.receipt_no, fc.fees_html')
                    ->from('fees_collect as fc')
                    ->join('fees_receipt as fr', function ($join) {
                        $join->whereRaw('FIND_IN_SET(fc.id, fr.FEES_ID) AND fr.SUB_INSTITUTE_ID = fc.sub_institute_id');
                    })
                    ->where('fc.sub_institute_id', $sub_institute_id)
                    ->where('fc.syear', $syear)
                    ->where('fc.student_id', $student_id)
                    ->where('fc.receipt_no', $receipt_id)
                    ->whereRaw("(fr.RECEIPT_ID_1 = '" . $receipt_id . "' OR fr.RECEIPT_ID_2 = '" . $receipt_id . "' OR fr.RECEIPT_ID_3 = '" . $receipt_id . "'
            OR fr.RECEIPT_ID_4 = '" . $receipt_id . "' OR fr.RECEIPT_ID_5 = '" . $receipt_id . "' OR fr.RECEIPT_ID_6 = '" . $receipt_id . "' OR fr.RECEIPT_ID_7 = '" . $receipt_id . "' OR fr.RECEIPT_ID_8 = '" . $receipt_id . "'
            OR fr.RECEIPT_ID_9 = '" . $receipt_id . "' OR fr.RECEIPT_ID_10 = '" . $receipt_id . "')")
                    ->groupBy('fc.fees_html')
                    ->unionAll(
                        DB::table('fees_paid_other as fo')
                            ->selectRaw('fo.id, fo.student_id, fo.reciept_id AS receipt_no, fo.paid_fees_html AS fees_html')
                            ->join('fees_receipt as fro', function ($join) {
                                $join->whereRaw('FIND_IN_SET(fo.id, fro.OTHER_FEES_ID) AND fro.SUB_INSTITUTE_ID = fo.sub_institute_id');
                            })
                            ->where('fo.sub_institute_id', $sub_institute_id)
                            ->where('fo.syear', $syear)
                            ->where('fo.student_id', $student_id)
                            ->where('fo.reciept_id', $receipt_id)
                            ->whereRaw("(fro.RECEIPT_ID_1 = '" . $receipt_id . "' OR fro.RECEIPT_ID_2 = '" . $receipt_id . "' OR fro.RECEIPT_ID_3 = '" . $receipt_id . "' OR fro.RECEIPT_ID_4 = '" . $receipt_id . "' OR fro.RECEIPT_ID_5 = '" . $receipt_id . "' OR fro.RECEIPT_ID_6 = '" . $receipt_id . "' OR fro.RECEIPT_ID_7 = '" . $receipt_id . "' OR fro.RECEIPT_ID_8 = '" . $receipt_id . "'
                    OR fro.RECEIPT_ID_9 = '" . $receipt_id . "' OR fro.RECEIPT_ID_10 = '" . $receipt_id . "')")
                            ->groupBy('fo.paid_fees_html')
                    );
            })
                ->selectRaw('student_id, receipt_no, fees_html')
                ->groupBy('student_id')
                ->get()
                ->toArray();

            $fees_collection_data = json_decode(json_encode($get_data), true);
            if (count($fees_collection_data) > 1) {
                $fees_receipt_html = $fees_collection_data;
            } else {
                $fees_collection_data = $fees_collection_data[0];
                $fees_receipt_html = $fees_collection_data['fees_html'];
            }
        }

        return $fees_receipt_html;
    }

    public function get_FeesHtmlForBulk($action, $inserted_id)
    {

        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $html_array = array();

        if ($action == 'other_fees_collect_receipt') {

            $get_data = DB::table('fees_other_collection')
                ->where('sub_institute_id', $sub_institute_id)
                ->where('syear', $syear)
                ->where('id', $inserted_id)->get()->toArray();

            $fees_other_collection_data = json_decode(json_encode($get_data), true);
            $fees_other_collection_data = $fees_other_collection_data[0];

            $html_array['student_id'] = $fees_other_collection_data['student_id'];
            $html_array['fees_receipt_html'] = $fees_other_collection_data['paid_fees_html'];
        }

        if ($action == 'imprest_fees_cancel_refund_receipt') {
            $get_data = DB::table('imprest_fees_cancel')
                ->where('sub_institute_id', $sub_institute_id)
                ->where('syear', $syear)
                ->where('id', $inserted_id)->get()->toArray();

            $imprest_fees_cancel_data = json_decode(json_encode($get_data), true);
            $imprest_fees_cancel_data = $imprest_fees_cancel_data[0];

            $html_array['student_id'] = $imprest_fees_cancel_data['student_id'];
            $html_array['fees_receipt_html'] = $imprest_fees_cancel_data['cancel_fees_html'];
        }

        if ($action == 'fees_circular') {
            $get_data = DB::table('fees_circular_log')
                ->where('sub_institute_id', $sub_institute_id)
                ->where('syear', $syear)
                ->where('id', $inserted_id)->get()->toArray();

            $fees_circular_data = json_decode(json_encode($get_data), true);
            $fees_circular_data = $fees_circular_data[0];

            $html_array['student_id'] = $fees_circular_data['STUDENT_ID'];
            $html_array['fees_receipt_html'] = $fees_circular_data['FEES_CIRCULAR_HTML'];
        }

        $certificate_type = DB::table('template_master')
            ->where('sub_institute_id', $sub_institute_id)
            ->pluck('title') // Assuming 'certificate_type' is a column in the table
            ->toArray(); // Convert the collection to an array

        if (in_array($action, $certificate_type)) {
            $get_data = DB::table('certificate_history')
                ->where('sub_institute_id', $sub_institute_id)
                ->where('syear', $syear)
                ->where('id', $inserted_id)->get()->toArray();

            $student_certificate_data = json_decode(json_encode($get_data), true);
            $student_certificate_data = $student_certificate_data[0];

            $html_array['student_id'] = $student_certificate_data['student_id'];
            $html_array['fees_receipt_html'] = $student_certificate_data['certificate_html'];
        }

        return $html_array;
    }

    public function get_FeesCss($action)
    {
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');

        $fees_config = DB::table('fees_config_master as fc')
            ->join('fees_receipt_css as frc', function ($join) {
                $join->whereRaw('frc.receipt_id = fc.fees_receipt_template');
            })->selectRaw('fc.* ,frc.css')
            ->where('fc.sub_institute_id', $sub_institute_id)
            ->where('fc.syear', $syear)->get()->toArray();

        if (count($fees_config) > 0) {
            $receipt_css = $fees_config[0]->css;
        } else {
            $fees_config = DB::table('fees_receipt_css')->where('receipt_id', 'A5')->select('css')->get()->toArray();
            $receipt_css = $fees_config[0]->css;
        }

        if ($action == 'imprest_ledger_view' || $action == 'Bonafide' || $action == 'Character Certificate' || $action == 'Transfer Certificate' || $action == 'certificate_re_receipt' || $action == 'other_fees_re_receipt') {
            $fees_receipt_css = '';
        } else {
            $fees_receipt_css = $receipt_css;
        }

        return $fees_receipt_css;
    }

    public function get_PageSetup($paper_size)
    {
        $sub_institute_id = session()->get('sub_institute_id');
        $four_res = [239];
        $syear = session()->get('syear');

        $extra_html = '';
        if ($paper_size == "A5") {
            $extra_html = ' <div>
                                <page size="A5" layout="landscape">
                                   ##HTML_SEC##
                                </page>
                            </div>';
        } elseif ($paper_size == "A5DB") {
            $extra_html = ' <div>
                                <page size="A5" layout="landscape">
                                    <table width="100%">
                                        <tr>
                                            <td style="width:50%">
                                                ##HTML_SEC##
                                            </td>
                                            <td style="width:50%;">
                                                 ##HTML_SEC##
                                            </td>
                                        </tr>
                                    </table>
                                </page>
                            </div>';
        } elseif ($paper_size == "A4") {
            $extra_html = ' <div style="padding:20px !important">
                                <page size="A4">
                                   ##HTML_SEC##
                                </page>
                            </div>';
        } elseif ($paper_size == "A4DB") {
            if (in_array($sub_institute_id, $four_res)) {
                $extra_html = ' <div>
                                <page size="A4">
                                <div>
                                   ##HTML_SEC##
                                </div>
                                <div style="page-break-after: always !important;"></div>
                                     <div style="margin-top:20px">
                                   ##HTML_SEC##
                                </div>
                                </page>
                            </div>';
            } else {
                $extra_html = ' <div>
                                <page size="A4" layout="landscape">
                                    <table width="100%">
                                        <tr>
                                            <td style="width:50%">
                                                ##HTML_SEC##
                                            </td>
                                            <td style="width:50%;">
                                                 ##HTML_SEC##
                                            </td>
                                        </tr>
                                    </table>
                                </page>
                            </div>';
            }
        } else {
            $extra_html = '<div>
                                ##HTML_SEC##
                           </div>';
        }
        return $extra_html;
    }

    public function htmlToPDF_making($paper_size, $html_file_path, $pdf_file_path)
    {
        if ($paper_size == "A5") {
            htmlToPDFLandscapeCertificate($html_file_path, $pdf_file_path);
        } elseif ($paper_size == "A4") {
            htmlToPDFPortrait($html_file_path, $pdf_file_path);
        } elseif ($paper_size == "A5DB") {
            htmlToPDFLandscape($html_file_path, $pdf_file_path);
        } elseif ($paper_size == "A4DB") {
            //htmlToPDFPortrait($html_file_path, $pdf_file_path);
            htmlToPDFLandscape($html_file_path, $pdf_file_path);
        } else {
            htmlToPDF($html_file_path, $pdf_file_path);
        }
    }

    public function searchMenu(Request $request)
    {
        $searchValue = $request->input('value');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $user_id = $request->session()->get('user_id');

        $rightsQuery = DB::table('tbluser as u')
            ->leftJoin('tblindividual_rights as i', function ($join) {
                $join->whereRaw('u.id = i.user_id AND u.sub_institute_id = i.sub_institute_id');
            })->leftJoin('tblgroupwise_rights as g', function ($join) {
                $join->whereRaw('u.user_profile_id = g.profile_id AND u.sub_institute_id = g.sub_institute_id');
            })->join('tblmenumaster as m', function ($join) use ($sub_institute_id) {
                $join->whereRaw("(i.menu_id = m.id OR g.menu_id = m.id) AND FIND_IN_SET(" . $sub_institute_id . ", m.sub_institute_id)");
            })->selectRaw('GROUP_CONCAT(distinct m.id) AS MID')
            ->where('u.sub_institute_id', $sub_institute_id)->where('u.id', $user_id)->get()->toArray();

        $rightsQuery = array_map(function ($value) {
            return (array)$value;
        }, $rightsQuery);

        $rightsMenusIds = 0;

        if (isset($rightsQuery['0']['MID'])) {
            $rightsMenusIds = $rightsQuery['0']['MID'];
        }

        return tblmenumasterModel::where('parent_menu_id', '!=', 0)
            ->whereRaw("find_in_set('$sub_institute_id',sub_institute_id) AND LEVEL IN (2,3) AND link != 'javascript:void(0);' AND id IN (" . $rightsMenusIds . ") AND status = 1 AND NAME LIKE '%" . $searchValue . "%' ")
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    public function get_search_url(Request $request)
    {
        return route($_REQUEST['value']);
    }

    public function get_bloom_texonomy(Request $request)
    {
        $question = $request->question;

        if ($question) {
            $url = 'https://getbloomslevel-gyzqqaohja-el.a.run.app';
            $headers = ['Accept: application/json'];

            $fields = ['str' => $question];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

            $result = curl_exec($ch);
            curl_close($ch);


            return $result;
        }
    }
    public function collectsct(Request $req)
    {
        $option = '<option>Select</option>';
        if ($req->sectionId == 1) {
            $academy = academic_sectionModel::where('sub_institute_id', $req->session()->get('sub_institute_id'))->get(['id', 'title', 'short_name', 'sort_order', 'shift', 'medium']);
            foreach ($academy as $row) {
                $option .= '<option value=' . $row['id'] . '>' . $row['title'] . '</option>';
            }
        } else if ($req->sectionId == 2) {
            $std = standardModel::where('sub_institute_id', $req->session()->get('sub_institute_id'))->get(['id', 'short_name']);
            foreach ($std as $row) {
                $option .= '<option value=' . $row['id'] . '>' . $row['short_name'] . '</option>';
            }
        } else if ($req->sectionId == 3) {
            $divs = divisionModel::where('sub_institute_id', $req->session()->get('sub_institute_id'))->get(['id', 'name']);
            foreach ($divs as $row) {
                $option .= '<option value=' . $row['id'] . '>' . $row['name'] . '</option>';
            }
        } else if ($req->sectionId == 5) {
            $std = standardModel::where(['sub_institute_id' => $req->session()->get('sub_institute_id'), 'grade_id' => $req->grade])->get(['id', 'short_name']);
            foreach ($std as $row) {
                $option .= '<option value=' . $row['id'] . '>' . $row['short_name'] . '</option>';
            }
        }
        return $option;
    }
    public function check_access(Request $request)
    {
        $userProfileId = session()->get('user_profile_id');
        $user_id = session()->get('user_id');
        $menu_id = $request->input("menu_id");
        $permissions = [];

        $individual = DB::table('tblindividual_rights')->where('menu_id', $menu_id)
            ->where('profile_id', $userProfileId)
            ->where('user_id', $user_id)
            ->where('sub_institute_id', session()->get('sub_institute_id'))
            ->first();

        $group = DB::table('tblgroupwise_rights')->where('menu_id', $menu_id)
            ->where('profile_id', $userProfileId)
            ->where('sub_institute_id', session()->get('sub_institute_id'))
            ->first();

        if (!empty($individual)) {
            $permissions = $individual;
        } else {
            $permissions = $group;
        }

        if ($permissions) {
            return response()->json([
                'can_view' => $permissions->can_view,
                'can_edit' => $permissions->can_edit,
                'can_add' => $permissions->can_add,
                'can_delete' => $permissions->can_delete,
            ]);
        }
    }

    public function chat(Request $request)
    {
        // return $request;exit;
        $question = $request->question;
        $standard = $request->standard;
        $type_name = $request->type_depth;
        $type_bloom = $request->type_bloom;
        $type_learning = $request->type_learning;

        if ($request->has('question') && $question !== '') {
            if ($request->type_depth) {
                $options = DB::table('lms_mapping_type')->select(DB::raw('group_concat(name) as type_name'))->where('parent_id', $request->type_depth)->first();
                $depth = "'" . $question . "' give answer from given options in one word this question for standard '" . $standard . "' student from these options $options->type_name ";
                $reason_depth = "if its one of $options->type_name then why it is give reason";
            }
            if ($request->type_bloom) {
                $options = DB::table('lms_mapping_type')->select(DB::raw('group_concat(name) as type_name'))->where('parent_id', $request->type_bloom)->first();
                $bloom = "'" . $question . "' give answer from given options in one word this question for Blooms Taxonomy? from these options $options->type_name";
                $reason_bloom = "if its one of $options->type_name then why it is give reason from this reasons 'factual','conceptual','procedural','metacoganitive'";
            }
            if ($request->type_learning) {
                // $options = DB::table('lms_mapping_type')->select(DB::raw('group_concat(name) as type_name'))->where('parent_id',$request->type_learning)->first();
                $learning = "'" . $question . "' according to this question What will be the learning outcome for standard '" . $standard . "' student ?";
                // $reason_learning = "if its one of $options->type_name then why it is give reason";                
            }
            $message = array(
                array(
                    "question_depth" => $depth,
                    "reason_depth" => $reason_depth,
                    "question_bloom" => $bloom,
                    "reason_bloom" => $reason_bloom,
                    "question_learning" => $learning
                ),
                // array("reason_learning"=>$reason_learning),                                 
                // array("always give  questions answer in one array with vlaue only")               
            );
        } else {
            $message = array($request->message);
        }
        $apiKey = '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';

        $endpoint = "https://api.openai.com/v1/chat/completions";

        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => json_encode($message)
                ]
            ],
            "temperature" => 0.7,
            "max_tokens" => 256,
            "top_p" => 1,
            "frequency_penalty" => 0,
            "presence_penalty" => 0,
            "stop" => ["11."]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($endpoint, $data)->json();

        if (isset($response['choices'][0]['message']['content'])) {

            $res['answer'] = $response['choices'][0]['message']['content'];
        } else {
            $res['answer'] = $response;
        }
        return $res['answer'];
    }

    public function getLectureList(Request $request)
    {
        $subject_id = $request->subject_id;
        $standard_id = $request->standard_id;
        $division_id = $request->division_id;
        $date = $request->date;

        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $entered_day = date('l', strtotime($date));

        $week_day = ["Monday" => "M", "Tuesday" => "T", "Wednesday" => "W", "Thursday" => "H", "Friday" => "F", "Saturday" => "S"];

        $timetable = DB::table('timetable as tt')
            ->select('tt.id as timetable_id', 'tt.academic_section_id as grade_id', 'tt.standard_id', 'tt.division_id', 'tt.subject_id', 'tt.period_id', 'tt.batch_id', 'tt.teacher_id', 'tt.week_day', 'tt.type', 'grd.title as grade_name', 'std.name as std_name', 'd.name as div_name', DB::raw('GROUP_CONCAT(ifnull(b.title, "-")) as batch_name'), DB::raw('GROUP_CONCAT(b.id) as batch_ids'), 'p.title as period_name')
            ->join('academic_section as grd', 'grd.id', '=', 'tt.academic_section_id')
            ->join('standard as std', 'std.id', '=', 'tt.standard_id')
            ->join('division as d', 'd.id', '=', 'tt.division_id')
            ->join('period as p', 'p.id', '=', 'tt.period_id')
            ->leftJoin('batch as b', 'b.id', '=', 'tt.batch_id')
            ->where('tt.sub_institute_id', $sub_institute_id)
            ->where('tt.syear', $syear)
            ->where('tt.subject_id', $subject_id)
            ->where('tt.standard_id', $standard_id)
            ->where('tt.division_id', $division_id)
            ->where('tt.week_day', $week_day[$entered_day]);
        // echo $entered_day;exit;

        if (session()->get('user_profile_name') == "Lecturer") {
            $timetable->where('tt.teacher_id', session()->get('user_id'));
        }

        $timetable = $timetable->get();

        return $timetable;
    }

    public function get_batch(Request $request)
    {
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $standard = $request->get('standard');
        $division = $request->get('division');

        $batch_data = DB::table('batch')->where(['sub_institute_id' => $sub_institute_id, 'syear' => $syear])->where('standard_id', $standard)->where('division_id', $division)->get()->toArray();

        return $batch_data;
    }

    // department wise emp 
    public function getDepEmployeeLists(Request $request)
    {
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $department_id = explode(',', $request->get('department_id'));
        // 02-08-2024 
        $userId = session()->get('user_id');
        $userProfileName = session()->get('user_profile_name');
        $SubCordinates = [];
        $profileArr = ["Admin", "Super Admin", "School Admin", "Assistant Admin"];
        if (!in_array($userProfileName, $profileArr)) {
            $SubCordinates = getSubCordinates($sub_institute_id, $userId);
        }
        // end 02-08-2024
        $employees = DB::table('tbluser')->join('tbluserprofilemaster as upm', 'upm.id', '=', 'tbluser.user_profile_id')
            ->selectRaw('tbluser.id,CONCAT_WS(" ",COALESCE(tbluser.first_name, "-"),COALESCE(tbluser.last_name, "-")) as full_name, tbluser.sub_institute_id, IfNULL(upm.name,"-") as user_profile')
            ->where('tbluser.sub_institute_id', $sub_institute_id)
            ->whereRaw('tbluser.department_id in (' . implode(',', $department_id) . ') ')
            ->where('tbluser.status', 1)
            ->when(!empty($SubCordinates), function ($q) use ($SubCordinates) {
                $q->whereIn('tbluser.id', $SubCordinates);
            })
            ->orderBy('tbluser.first_name')
            ->groupBy('tbluser.id')
            ->get()
            ->toArray();

        return $employees;
    }
    public function studentLists(Request $request)
    {
        $grade = $request->grade;
        $standard = $request->standard;

        $div = "";
        $sub_institute_id = "";
        $syear = "";
        $roll_no = $request->roll_no;
        $stu_name = $request->stu_name;
        $uniqueid = $request->uniqueid;
        $mobile = $request->mobile;
        $grno = $request->grno;
        $stud_id = $request->stud_id;
        $batch = $request->batch;
        $status = $request->status;

        if (isset($request->division)) {
            $div = $request->division;
        }
        if (isset($request->module) && $request->module == 'admission_enquiry') {
            $sub_institute_id = isset($request->sub_institute_id) ? $request->sub_institute_id : session()->get('sub_institute_id');

            $dataList = DB::table('tblstudent as ts')
                ->Join('tblstudent_enrollment as se', function ($q) use ($sub_institute_id) {
                    $q->on('se.student_id', '=', 'ts.id')
                        ->where('se.sub_institute_id', $sub_institute_id);
                })
                ->selectRaw('ts.*')
                ->where('ts.sub_institute_id', $sub_institute_id)
                ->where(function ($query) use ($stu_name) {
                    $query->where('ts.first_name', 'like', '%' . $stu_name . '%')
                        ->orWhere('ts.middle_name', 'like', '%' . $stu_name . '%')
                        ->orWhere('ts.last_name', 'like', '%' . $stu_name . '%');
                })
                ->whereNull('se.end_date')
                ->groupBy('ts.id')
                ->get()->toArray();
        } else {
            $dataList = SearchStudent($grade, $standard, $div, $sub_institute_id, $syear, $roll_no, $stu_name, $uniqueid, $mobile, $grno, $stud_id, $batch, $status);
        }

        return $dataList;
    }

    public function getCOData(Request $request)
    {
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');

        $data = DB::table('lo_category')->where(['sub_institute_id' => $sub_institute_id, 'grade_id' => $request->grade_id, 'standard_id' => $request->standard_id, 'subject_id' => $request->subject_id, 'show_hide' => 1])->orderBy('sort_order')->get()->toArray();

        return $data;
    }
}
