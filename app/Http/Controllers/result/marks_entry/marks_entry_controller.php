<?php

namespace App\Http\Controllers\result\marks_entry;

use App\Http\Controllers\Controller;
use App\Models\result\create_exam\exam_creation;
use App\Models\result\marks_entry\marks_entry;
use App\Models\result\std_grd_mapping\std_grd_maping;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function App\Helpers\is_mobile;
use function App\Helpers\SearchStudent;

class marks_entry_controller extends Controller
{
    use GetsJwtToken;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (session()->has('data')) { // check if it exists
            $data_arr = session('data'); // to retrieve value
            if (isset($data_arr['message'])) {
                $data['message'] = $data_arr['message'];
            }
            if (isset($data_arr['class'])) {
                $data['class'] = $data_arr['class'];
            }
        }

        $data['data'] = [];
        $type = $request->input('type');

        return is_mobile($type, "result/marks_entry/show", $data, "view");
    }
    public function approve(Request $request)
    {
        // return $request;exit;
        $sub_institute_id = session()->get('sub_institute_id');
        $term_id=$request->term_id;
        $standard_id = $request->standard_id;
        $division_id = $request->division_id;
        $subject_id = $request->subject_id;
        $exam_id = $request->exam_id;
        $user = session()->get('user_id');        
        $module_name = "result_mark";

        $data=[
            "subject_id"=>$subject_id,
            "standard_id"=>$standard_id,
            "division_id"=>$division_id,
            "exam_id"=>$exam_id,
            "term_id"=>$term_id,      
            "sub_institute_id"=>$sub_institute_id,      
            "module_name"=>$module_name,
        ];
    
        $check = DB::table('result_exam_approve')->where($data)->get()->toArray();

        if(!empty($check) && $check > 0){
            $query = DB::table('result_exam_approve')->where($data)->update(['status'=>$request->approve ?? 0,'created_by'=>$user,'updated_at'=>now()]);
            $res = [
                "status_code" => 1,
                "message"     => "Data Upadted",
                "class"       => "success",
            ];
        }else{
            $data += ['status'=>$request->approve ?? 0,'created_by'=>$user,'created_at'=>now()];            
            $query = DB::table('result_exam_approve')->insert($data);
            $res = [
                "status_code" => 1,
                "message"     => "Data Saved",
                "class"       => "success",
            ];
        }
   
        $type = $request->input('type');

        return is_mobile($type, "marks_entry.index", $res, "redirect");
    }
    public function get_marks_dd()
    {
        $sub_institute_id = $_REQUEST["sub_institute_id"];
        $syear = $_REQUEST["syear"];
        $student_id = $_REQUEST["student_id"];

        $result = DB::table("result_exam_type_master as etm")
            ->join('result_exam_master as em', function ($join) {
                $join->whereRaw("em.ExamType = etm.Id");
            })
            ->join('result_create_exam as rce', function ($join) {
                $join->whereRaw("rce.exam_id = em.Id");
            })
            ->join('result_marks as rm', function ($join) use ($student_id) {
                $join->whereRaw("rm.student_id = $student_id");
            })
            ->selectRaw('etm.ExamType,em.ExamTitle,rce.title')
            ->where("rm.sub_institute_id", "=", $sub_institute_id)
            ->where("rce.syear", "=", $syear)
            ->groupBy('em.ExamType')
            ->get()->toarray();

//        echo ('<pre>');print_r($query);exit;

//         SELECT s.subject_name,rce.points f_marks,rm.points g_marks, SUM(rce.points) tf_marks, SUM(rm.points) tg_marks,
        // (100 * SUM(rm.points))/ SUM(rce.points) AVG
        // FROM result_create_exam rce
        // INNER JOIN subject s ON s.id = rce.subject_id
        // INNER JOIN result_marks rm ON rm.exam_id = rce.id
        // WHERE title = "Test Exam" AND rm.student_id = 3117

        // $data = json_encode($result);

        $responce_arr = [
            "status"  => "1",
            "message" => "Sucsess",
            "data"    => $result,
        ];

        echo json_encode($responce_arr);
        exit;
    }

    public function get_co_scholastic_marks_dd(Request $request)
    {
        try {
            if (! $this->jwtToken()->validate()) {
                $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];

            return response()->json($response, 200);
        }
        $response = ['data' => '', 'status' => '0', 'message' => 'Failuer'];
        $validator = Validator::make($request->all(), [
            'teacher_id'       => 'required|numeric',
            'sub_institute_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $response['message'] = $validator->messages();
        } else {
            $sub_institute_id = $_REQUEST["sub_institute_id"];
            $teacher_id = $_REQUEST["teacher_id"];

            $result = DB::table("timetable as t")
                ->join('standard as s', function ($join) {
                    $join->whereRaw("s.id = t.standard_id");
                })
                ->join('division as d', function ($join) {
                    $join->whereRaw("d.id = t.division_id");
                })
                ->join('result_co_scholastic_parent as rcp', function ($join) {
                    $join->whereRaw("rcp.sub_institute_id = 46");
                })
                ->join('result_co_scholastic as rc', function ($join) {
                    $join->whereRaw("rc.parent_id = rcp.id");
                })
                ->join('result_co_scholastic_grades', function ($join) {
                    $join->whereRaw("");
                })
                ->join('academic_year as ay', function ($join) {
                    $join->whereRaw("ay.term_id = rc.term_id");
                })
                ->selectRaw('CONCAT_WS(' / ',s.name,d.name,ay.title,rcp.title,rc.title) resp_data,
    s.id AS standard_id, d.id AS division_id,rc.mark_type,rc.term_id,rc.id co_scholastic_id, s.grade_id acdemic_section_id,
    rc.co_grade,rc.max_mark')
                ->where("t.sub_institute_id", "=", $sub_institute_id)
                ->where("t.teacher_id", "=", $teacher_id)
                ->groupByRaw('t.standard_id,t.division_id,rc.term_id')
                ->orderBy('t.standard_id')
                ->get()->toarray();

            $send_data = $result;

            foreach ($result as $id => $arr) {
                if ($arr->mark_type == 'GRADE') {
                    $map_id = $arr->co_grade;

                    $grade_result = DB::table('result_co_scholastic_grades')->where('map_id',
                        $map_id)->get()->toArray();

                    $send_data[$id]->grades = $grade_result;
                } else {
                    $send_data[$id]->grades = [];
                }
            }
        }

        $responce_arr = [
            "status"  => "1",
            "message" => "Sucsess",
            "data"    => $send_data,
        ];

        echo json_encode($responce_arr);
        exit;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  Request  $request
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return Response
     */
    public function create(Request $request)
{
    // Get exam creations based on exam or exam_master
    $exam_creations = exam_creation::select('id', 'points')
        ->when($request->exam != '', function ($q) use ($request) {
            $q->where('id', $request->exam);
        }, function ($q) use ($request) {
            $q->where('exam_id', $request->exam_master);
        })->get();

    $student_data = SearchStudent($request->grade, $request->standard, $request->division);
    $grd_data = $this->getGreadData($request->standard);

    // Get marks entries
    $marks_entries = marks_entry::where('sub_institute_id', session()->get('sub_institute_id'))
        ->when($request->exam != '', function ($q) use ($request) {
            $q->where('exam_id', $request->exam);
        })->get();

    // Build response array
    $responce_arr = [
        'term_id' => $request->term,
        'standard' => $request->standard,
        'grade' => $request->grade,
        'division' => $request->division,
        'subject_dd' => $this->getSubjectDD($request->standard),
        'subject' => $request->subject,
        'exam_master' => $request->exam_master,
        'exam_dd' => $this->getExamDD($request->term, $request->standard, $request->subject, $request->exam_master),
        'exam' => $request->exam,
        'grd_data' => $grd_data
    ];

    // Check approval status
    $approve_status = [
        "subject_id" => $request->subject,
        "standard_id" => $request->standard,
        "division_id" => $request->division,
        "exam_id" => $request->exam,
        "term_id" => $request->term,
        "sub_institute_id" => session()->get('sub_institute_id'),
        "module_name" => "result_mark"
    ];

    $check_approve = DB::table('result_exam_approve')->where($approve_status)->first();
    if (isset($check_approve->created_by)) {
        $approved_user = DB::table('tbluser')
            ->where('id', $check_approve->created_by)
            ->where('status', 1)
            ->first();
    }

    $responce_arr['approve_status'] = $check_approve;
    $responce_arr['approved_user'] = $approved_user ?? '';
    //dd($student_data);
    // Process student data
    if (!empty($student_data)) {
        // ✅ Show only failed students if checkbox selected
        if ($request->has('is_remedial') && $request->is_remedial == 1) {
            $failed_students = DB::table('result_marks as rm')
            ->join('result_create_exam as rce', 'rm.exam_id', '=', 'rce.id')
            ->join('tblstudent_enrollment as se', function ($join) {
                $join->on('rm.student_id', '=', 'se.student_id')
                    ->on('rce.standard_id', '=', 'se.standard_id')
                    ->on('rce.sub_institute_id', '=', 'se.sub_institute_id');
            })
            ->where('rm.sub_institute_id', session()->get('sub_institute_id'))
            ->where('rce.exam_id', $request->exam_master)
            ->where('rce.term_id', $request->term)
            ->where('rce.standard_id', $request->standard)
            ->where('rce.subject_id', $request->subject)
            ->where('se.section_id', $request->division)
            ->where('se.syear', session()->get('syear'))
            ->select(
                'rm.student_id',
                'se.grade_id',
                'se.standard_id',
                'se.section_id',
                DB::raw('SUM(rm.points) AS obtained_marks'),
                DB::raw('SUM(rce.points) AS total_marks'),
                DB::raw('MAX(rce.cutoff) AS cutoff_reference')
            )
            ->groupBy('rm.student_id', 'se.grade_id', 'se.standard_id', 'se.section_id')
            ->havingRaw('SUM(rm.points) < MAX(rce.cutoff)')
            ->pluck('rm.student_id')
            ->toArray();
            
                // ✅ Fix: match by integer comparison
            $student_data = array_values(array_filter($student_data, function ($student) use ($failed_students) {
                $id = isset($student['student_id'])
                    ? (int)$student['student_id']
                    : (int)$student['id'];
                return in_array($id, $failed_students);
            }));
        }
        
        // ✅ Continue with your normal mapping logic
        foreach ($student_data as $student) {
            $student_id = (int)($student['student_id'] ?? $student['id']);
            $responce_arr['stu_data'][$student_id] = [];
            $responce_arr['stu_data'][$student_id]['enrollment_no'] = $student['enrollment_no'];
            $responce_arr['stu_data'][$student_id]['name'] =
                $student['first_name'] . ' ' .
                $student['middle_name'] . ' ' .
                $student['last_name'];

            // ✅ Check if student has elective subject
            $has_elective = DB::table("sub_std_map as sm")
                ->where([
                    "sm.sub_institute_id" => session()->get('sub_institute_id'),
                    "sm.standard_id" => $request->standard,
                    "sm.subject_id" => $request->subject,
                    "sm.allow_grades" => 'Yes',
                    "sm.elective_subject" => 'Yes'
                ])->exists();

            if ($has_elective) {
                $is_opted = DB::table("student_optional_subject")
                    ->where([
                        "student_id" => $student_id,
                        "subject_id" => $request->subject,
                        "syear" => session()->get('syear')
                    ])->exists();

                if (!$is_opted) {
                    continue;
                }
            }

            // ✅ Map exam results
            foreach ($exam_creations as $exam) {
                $marks = $marks_entries->where('student_id', $student_id)
                    ->where('exam_id', $exam->id)
                    ->first();

                $responce_arr['stu_data'][$student_id][$exam->id] = [
                    'points' => $marks ? $marks->points : 0,
                    'outof' => $exam->points,
                    'is_absent' => $marks ? $marks->is_absent : null,
                    'per' => $marks ? $marks->per : 0,
                    'grade' => $marks ? $marks->grade : '-',
                    'comment' => $marks ? $marks->comment : ''
                ];
            }
        }
    }

    // Return to view
    return is_mobile($request->input('type'), "result/marks_entry/add", $responce_arr, "view");
}

    public function getSubjectDD($std)
    {
        $where = [
            "sub_std_map.sub_institute_id" => session()->get('sub_institute_id'),
            "sub_std_map.allow_grades"     => "Yes",
        ];

        $where['sub_std_map.standard_id'] = $std;

        return DB::table('subject')
            ->join('sub_std_map', 'subject.id', '=', 'sub_std_map.subject_id')
            ->where($where)
            ->orderBy('sub_std_map.sort_order')
            ->pluck('sub_std_map.display_name', 'subject.id');
    }

    public function getExamDD($term, $std, $sub,$examId='')
    {
        $where = [
            "re.sub_institute_id" => session()->get('sub_institute_id'),
            "re.syear"            => session()->get('syear'),
            "re.term_id"          => $term,
            "re.standard_id"      => $std,
            "re.subject_id"       => $sub,
            're.exam_id'          => $examId, // added by uma on 20-04-2025
        ];

        return DB::table('result_create_exam as re')
            ->join('lo_category as lc', 'lc.id', '=', 're.co_id')
            ->where($where)
            ->pluck('re.title', 're.id');
    }

    public function get_result(Request $request)
    {
        try {
            if (! $this->jwtToken()->validate()) {
                $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];

            return response()->json($response, 200);
        }
        $response = ['data' => '', 'status' => '0', 'message' => 'Failuer'];
        $validator = Validator::make($request->all(), [
            'student_id'       => 'required|numeric',
            'sub_institute_id' => 'required|numeric',
            'syear'            => 'required|numeric',
        ]);
        $send_data = [];
        if ($validator->fails()) {
            $response['message'] = $validator->messages();
        } else {
            $sub_institute_id = $_REQUEST["sub_institute_id"];
            $student_id = $_REQUEST["student_id"];

            $exams = DB::table("result_exam_type_master as etm")
                ->join('result_exam_master as em', function ($join) {
                    $join->whereRaw("em.ExamType = etm.Id");
                })
                ->join('result_create_exam as rcm', function ($join) {
                    $join->whereRaw("rce.exam_id = em.Id");
                })
                ->join('subject as s', function ($join) {
                    $join->whereRaw("s.id = rce.subject_id");
                })
                ->join('result_marks as rm', function ($join) {
                    $join->whereRaw("rm.exam_id = rce.id");
                })
                ->selectRaw('CONCAT_WS(' - ',etm.ShortName,em.ExamTitle,rce.title) exam_name,
                etm.Id etmid,em.Id emid,rce.id rceid,rce.title')
                ->where("em.SubInstituteId", "=", $sub_institute_id)
                ->where("rm.student_id", "=", $student_id)
                ->groupBy('rce.title')
                ->get()->toArray();

            $exams = json_encode($exams);
            $exams = json_decode($exams, 1);

            $send_data = [];
            $i = 0;

            foreach ($exams as $id => $arr) {
                $exam_name = $arr["title"];

                $ret_result = DB::table("result_exam_type_master as etm")
                    ->join('result_exam_master as em', function ($join) {
                        $join->whereRaw("em.ExamType = etm.Id");
                    })
                    ->join('result_create_exam as rce', function ($join) {
                        $join->whereRaw("rce.exam_id = em.Id");
                    })
                    ->join('subject as s', function ($join) {
                        $join->whereRaw("s.id = rce.subject_id");
                    })
                    ->join('result_marks as rm', function ($join) {
                        $join->whereRaw("rm.exam_id = rce.id");
                    })
                    ->selectRaw('s.subject_name, rce.points f_marks, rm.points g_marks, SUM(rce.points) total_marks, 
                SUM(rm.points) totalk_get_marks,(SUM(rm.points)*100/ SUM(rce.points)) avge')
                    ->where("em.SubInstituteId", "=", $sub_institute_id)
                    ->where("rm.student_id", "=", $student_id)
                    ->where("rce.title", "=", $exam_name)
                    ->groupBy('s.subject_name')
                    ->get()->toArray();

                $send_data[$i]["exam_data"] = $arr;
                $send_data[$i]["exam_data"]["result"] = $ret_result;
                $i++;

            }

            $response = [
                "status"  => "1",
                "message" => "Sucsess",
                "data"    => $send_data,
            ];
        }

        echo json_encode($response);
        exit;
    }

    public function getGreadData($std)
    {
        $join = [
            "gd.grade_id"         => "rm.grade_scale",
            "gd.sub_institute_id" => "rm.sub_institute_id",
        ];
        $where = [
            "rm.sub_institute_id" => session()->get('sub_institute_id'),
            "rm.standard"         => $std,
            "gd.syear"            => session()->get('syear'),
        ];

        $data = std_grd_maping::from("result_std_grd_maping as rm")
            ->join("grade_master_data as gd", $join)
            ->where($where)->get()->toArray();
        $final_arr = [];

        if (count($data) > 0) {
            $temp_arr = [];
            foreach ($data as $id => $arr) {
                $temp_arr[$arr['breakoff']] = $arr['title'];
            }
            ksort($temp_arr);
            $i = 1;
            foreach ($temp_arr as $id => $val) {
                $farr[$i][$id] = $val;
                $i++;
            }

            $cnt = 1;
            $last_id = 0;
            // foreach ($farr as $id => $arrs) {
            //     foreach ($arrs as $bk => $val) {
            //         if ($id == 1) {
            //             $final_arr[$val] = range(0, $bk);
            //             continue;
            //         }
            //         if ($id == count($farr)) {
            //             $final_arr[$val] = range($bk + 1, 100);
            //             continue;
            //         }
            //         foreach ($farr[$id - 1] as $last_val => $vals) {
            //             $final_arr[$val] = range($last_val + 1, $bk);
            //         }
            //     }
            // }

            foreach ($farr as $id => $arrs) {
                foreach ($arrs as $bk => $val) {
                    if ($id != 1) {
                        $bk = $bk; // + 1
                    }
                    if ($id != count($farr)) {
                        $new_bk = key($farr[$id + 1]);
                        --$new_bk;
                        $final_arr[$val] = range($bk, $new_bk);
                    } else {
                        $final_arr[$val] = range($bk, 100);
                    }
                }
            }
        }

        return $final_arr;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return Response
     */
public function store(Request $request)
{
    $sub_institute_id = session()->get('sub_institute_id');
    $all_data = [];

    $is_remedial = $request->has('is_remedial') ? 1 : 0;

    if (isset($_REQUEST["type"]) && $_REQUEST["type"] == "API") {
        $sub_institute_id = $_REQUEST["sub_institute_id"];
        $all_data = json_decode($_REQUEST["data"], 1);
    } else {
        $all_data = $_REQUEST['values'];
    }

    foreach ($all_data as $student_id => $exam_data) {
        foreach ($exam_data as $exam_id => $arr) {
            $check = marks_entry::where([
                'sub_institute_id' => $sub_institute_id,
                'student_id' => $student_id,
                'exam_id' => $exam_id,
            ])->exists();
            // ✅ Common fields used in both insert and update
            $baseData = [
                'student_id' => $student_id,
                'exam_id' => $exam_id,
                'sub_institute_id' => $sub_institute_id,
                'is_remedial' => $is_remedial, // <-- store remedial flag
            ];

            if (!empty($check) && $check > 0) {
                if ($arr['points'] != '') {
                    if (preg_match("/[a-z]/i", $arr['points'])) {
                        if (strtoupper($arr['points']) == "AB" || strtoupper($arr['points']) == "N.A." || strtoupper($arr['points']) == "EX") {
                            $data = [
                                'student_id' => $student_id,
                                'exam_id' => $exam_id,
                                'points' => 0,
                                // 'per' => 0,
                                // 'grade' => $arr['grade'],
                                // 'comment' => $arr['comment'],
                                'is_absent' => $arr['points'],
                                'sub_institute_id' => $sub_institute_id,
                            ];
                        } else {
                            $data = [
                                'student_id' => $student_id,
                                'exam_id' => $exam_id,
                                'points' => 0,
                                // 'per' => 0,
                                // 'grade' => $arr['grade'],
                                // 'comment' => $arr['comment'],
                                'is_absent' => "AB",
                                'sub_institute_id' => $sub_institute_id,
                            ];
                        }
                        marks_entry::where([
                            'sub_institute_id' => $sub_institute_id,
                            'student_id' => $student_id,
                            'exam_id' => $exam_id,
                        ])->update($data);
                    } else {
                        // $arr['per'] = rtrim($arr['per'], '%');
                        $data = [
                            'student_id' => $student_id,
                            'exam_id' => $exam_id,
                            'points' => $arr['points'],
                            'is_absent' => '',
                            // 'per' => $arr['per'],
                            // 'grade' => $arr['grade'],
                            // 'comment' => $arr['comment'],
                            'sub_institute_id' => $sub_institute_id,
                        ];
                        marks_entry::where([
                            'sub_institute_id' => $sub_institute_id,
                            'student_id' => $student_id,
                            'exam_id' => $exam_id,
                        ])->update($data);
                    }
                }
                $res = [
                    "status_code" => 1,
                    "message" => "Data Updated",
                    "class" => "success",
                ];
            } else {
                if ($arr['points'] != '') {
                    if (preg_match("/[a-z]/i", $arr['points'])) {
                        if (strtoupper($arr['points']) == "AB" || strtoupper($arr['points']) == "N.A." || strtoupper($arr['points']) == "EX") {
                            $data = new marks_entry([
                                'student_id' => $student_id,
                                'exam_id' => $exam_id,
                                'points' => 0,
                                // 'per' => 0,
                                // 'grade' => "-",
                                // 'comment' => $arr['comment'],
                                'is_absent' => $arr['points'],
                                'sub_institute_id' => $sub_institute_id,
                            ]);
                            $data->save();
                        } else {
                            $data = new marks_entry([
                                'student_id' => $student_id,
                                'exam_id' => $exam_id,
                                'points' => 0,
                                // 'per' => 0,
                                // 'grade' => '-',
                                // 'comment' => $arr['comment'],
                                'is_absent' => "AB",
                                'sub_institute_id' => $sub_institute_id,
                            ]);
                            $data->save();
                        }
                    } else {
                        // $arr['per'] = rtrim($arr['per'], '%');
                        $data = new marks_entry([
                            'student_id' => $student_id,
                            'exam_id' => $exam_id,
                            'points' => $arr['points'],
                            // 'per' => $arr['per'],
                            // 'grade' => $arr['grade'],
                            // 'comment' => $arr['comment'],
                            'sub_institute_id' => $sub_institute_id,
                        ]);
                        $data->save();
                    }
                }
                $res = [
                    "status_code" => 1,
                    "message" => "Data Saved",
                    "class" => "success",
                ];
            }
        }
    }

    $type = $request->input('type');
    return is_mobile($type, "marks_entry.index", $res, "redirect");
}
    // marks approval report
    public function show(Request $request){
        if (session()->has('data')) { // check if it exists
            $data_arr = session('data'); // to retrieve value
            if (isset($data_arr['message'])) {
                $data['message'] = $data_arr['message'];
            }
            if (isset($data_arr['class'])) {
                $data['class'] = $data_arr['class'];
            }
        }

        $data['data'] = [];
        $type = $request->input('type');
        return is_mobile($type, "result/result_report/marks_approval_report", $data, "view");
    }

    public function getMarksApproval(Request $request){
        
        $responce_arr['term'] = $_REQUEST["term"];
        $responce_arr['standard'] = $_REQUEST["standard"];
        $responce_arr['grade'] = $_REQUEST['grade'];
        $responce_arr['division'] = $_REQUEST['division'];
        $responce_arr['subject_dd'] = $this->getSubjectDD($_REQUEST["standard"]);
        $sub_institute_id  = session()->get('sub_institute_id');
        $request = $_REQUEST;
        // get scholastic approval report
        //     $scholastic = DB::table('result_create_exam as cm')
    //    ->leftJoin('result_exam_master as em',function($join) use($request){
    //         $join->on('cm.exam_id','=','em.id');
    //     })
    //     ->leftJoin('result_exam_type_master as etm',function($join) use($request){
    //         $join->on('em.ExamType','=','etm.id');
    //     })->leftJoin('subject as sub',function($join) use($request){
    //             $join->on('sub.id','=','cm.subject_id');
    //     })
    //     ->selectRaw("em.ExamTitle as exam_name,group_concat(cm.title) as exam_title,group_concat(cm.exam_id) as c_exam,em.id as exam_id,sub.subject_name,cm.standard_id,etm.ExamType as exam_type,sub.id as subject_id")
    //     ->where(['cm.standard_id'=>$request['standard'],'cm.term_id'=>$request['term'],'cm.sub_institute_id'=>$sub_institute_id])
    //     ->groupByRaw('cm.subject_id')->get()->toArray();

    $scholastic = DB::table('result_create_exam as cm')
    ->leftJoin('result_exam_master as em', function ($join) use ($request) {
        $join->on('cm.exam_id', '=', 'em.id');
    })
    ->leftJoin('result_exam_type_master as etm', function ($join) use ($request) {
        $join->on('em.ExamType', '=', 'etm.id');
    })
    ->leftJoin('subject as sub', function ($join) use ($request) {
        $join->on('sub.id', '=', 'cm.subject_id');
    })
    ->selectRaw("em.ExamTitle as exam_name, group_concat(DISTINCT em.ExamType) as exam_type_id, group_concat(DISTINCT cm.title) as exam_title, group_concat(DISTINCT cm.exam_id) as create_exam, group_concat(DISTINCT cm.id) as exam_id,sub.subject_name, cm.standard_id, etm.ExamType as exam_type, sub.id as subject_id")
    ->where(['cm.standard_id' => $request['standard'], 'cm.term_id' => $request['term'], 'cm.sub_institute_id' => $sub_institute_id])
    ->groupByRaw('cm.subject_id,em.ExamType')
    ->get()
    ->toArray();

    
    $subjects = DB::table('result_create_exam as cm')
    ->join('subject as sub','sub.id','=','cm.subject_id')
    ->selectRaw('sub.id as sub_id,sub.subject_name')    
    ->where(['cm.standard_id' => $request['standard'], 'cm.term_id' => $request['term'], 'cm.sub_institute_id' => $sub_institute_id])
    ->groupByRaw('cm.subject_id')->get();
    $exam_type = DB::table('result_exam_type_master')->where(['SubInstituteId' => $sub_institute_id])->get();

    $co_scholastic = DB::table('result_co_scholastic_marks_entries as rcme')->join('result_co_scholastic as rcs','rcs.id','=','rcme.co_scholastic_id')
    ->selectRaw("rcme.id as create_id,rcme.standard_id,rcme.term_id,rcme.co_scholastic_id,rcs.id as main_id,rcs.title as exam_name")
    ->where(['rcme.standard_id' => $request['standard'], 'rcme.term_id' => $request['term'], 'rcme.sub_institute_id' => $sub_institute_id])
    ->groupByRaw('rcme.co_scholastic_id')->get();
        // dd($scholastic->toSql());
        //  $scholastic->get()->toArray();
        $grade_type = DB::table('result_co_scholastic')->where([ 'term_id' => $request['term'], 'sub_institute_id' => $sub_institute_id])->groupBy('mark_type')->selectRaw('group_concat(DISTINCT title) as title,mark_type,group_concat(DISTINCT id) as grade_id')->get();

        // echo "<pre>";print_r($grade_type);exit;
        $responce_arr['scholastic']=$scholastic;
        $responce_arr['subject_head']=$subjects;
        $responce_arr['exam_type']=$exam_type; 
        $responce_arr['grade_type']=$grade_type;                       
        $responce_arr['co_scholastic'] = $co_scholastic;

        $type = "";
        return is_mobile($type, "result/result_report/marks_approval_report", $responce_arr, "view");
    }


}
