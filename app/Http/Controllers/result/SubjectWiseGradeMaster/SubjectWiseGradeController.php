<?php

namespace App\Http\Controllers\result\SubjectWiseGradeMaster;

use App\Http\Controllers\Controller;
use App\Models\GradeSubjectWiseMaster;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function App\Helpers\is_mobile;

class SubjectWiseGradeController extends Controller
{
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
        }
        $data['data'] = $this->getData();

        $type = $request->input('type');

        return is_mobile($type, "result/grade_subject_wise_master/show_exam", $data, "view");
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
        $exams = DB::table("result_exam_master")
            ->where("SubInstituteId", session()->get('sub_institute_id'))
            ->pluck("ExamTitle", "Id")->toArray();
        $type = $request->input('type');

        return is_mobile($type, "result/grade_subject_wise_master/add_exam", $exams, "view");
    }

    public function getData()
{
    return DB::table('grade_subject_wise_master')
        ->join('academic_year', [
            'academic_year.term_id' => 'grade_subject_wise_master.term_id',
            'academic_year.sub_institute_id' => 'grade_subject_wise_master.sub_institute_id',
        ])
        ->select(
            'grade_subject_wise_master.id',
            'academic_year.title as term_name',
            'grade_subject_wise_master.subject',
            'grade_subject_wise_master.title',
            'grade_subject_wise_master.breakoff',
            'grade_subject_wise_master.sort_order',
            'grade_subject_wise_master.created_at',
            'grade_subject_wise_master.updated_at'
        )
        ->where([
            'grade_subject_wise_master.sub_institute_id' => session()->get('sub_institute_id'),
        ])
        ->get()->toArray();
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
        $eroor = false;
        $error_reson = "";

        $subject_id = $request->get('subject');
        $titles     = $request->get('title', []);
        $points     = $request->get('points', []);
        $sortOrders = $request->get('sort_order', []);
        $marksTypes = $request->get('marks_type', []);
        $reportStatuses = $request->get('report_card_status', []);

        // Loop through rows
        foreach ($titles as $key => $title) {
            if (empty($title)) {
                continue; // skip empty rows
            }

            $check = exam_creation::where([
                'syear'            => session()->get('syear'),
                'sub_institute_id' => session()->get('sub_institute_id'),
                'term_id'          => $request->get('term'),
                'exam_id'          => $request->get('exam'),
                'standard_id'      => $request->get('standard'),
                'subject_id'       => $subject_id,
                'co_id'            => $coIds[$key] ?? null,
                'title'            => $title,
            ])->exists();

            if ($check) {
                $eroor = true;
                $error_reson = "Exam already exists for selected Standard/Subject/CO with title '{$title}'.";
                break;
            }
        }

        if ($eroor === false) {
            foreach ($titles as $key => $title) {
                if (empty($title)) {
                    continue;
                }

                $exam = new exam_creation([
                    'syear'              => session()->get('syear'),
                    'sub_institute_id'   => session()->get('sub_institute_id'),
                    'term_id'            => $request->get('term'),
                    'medium'             => $request->get('medium'),
                    'exam_id'            => $request->get('exam'),
                    'standard_id'        => $request->get('standard'),
                    'app_disp_status'    => $request->get('app_disp_status'),
                    'subject_id'         => $subject_id,
                    'title'              => $title,
                    'points'             => $points[$key] ?? 0,
                    'con_point'          => $request->get('con_point'),
                    'marks_type'         => $marksTypes[$key] ?? 'MARKS',
                    'report_card_status' => $reportStatuses[$key] ?? 'Y',
                    'sort_order'         => $sortOrders[$key] ?? 0,
                    'co_id'              => $coIds[$key] ?? null,
                    'exam_date'          => !empty($examDates[$key])
                        ? date("Y-m-d", strtotime($examDates[$key]))
                        : null,
                    'created_by'         => session()->get('user_id'),
                    'created_at'         => now(),
                ]);
                $exam->save();
            }
        }

        $res = [
            "status_code" => $eroor ? 0 : 1,
            "message"     => $eroor ? $error_reson : "Exam Created Successfully !",
        ];

        $type = $request->input('type');
        return is_mobile($type, "grade_subject_wise_master.index", $res, "redirect");
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return void
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Request  $request
     * @param  int  $id
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return Response
     */
    public function edit(Request $request, $id)
    {
        $type = $request->input('type');
        $data = exam_creation::select(
            'result_create_exam.id',
            'result_create_exam.term_id',
            'result_create_exam.medium',
            'result_create_exam.exam_id',
            'result_create_exam.app_disp_status',
            'standard.grade_id as grade',
            'result_create_exam.standard_id',
            'result_create_exam.subject_id',
            'result_create_exam.title',
            'result_create_exam.points',
            'result_create_exam.marks_type',
            'result_create_exam.report_card_status',
            'result_create_exam.sort_order',
            'result_create_exam.exam_date',
            'result_create_exam.con_point',
            'result_create_exam.co_id'
        )
            ->join('standard', 'standard.id', '=', 'result_create_exam.standard_id')
            ->where([
                'result_create_exam.sub_institute_id' => session()->get('sub_institute_id'),
                'result_create_exam.id'               => $id,
            ])
            ->get()->toArray();
        $data = $data[0];

        $exams = DB::table("result_exam_master")
            ->where("SubInstituteId", session()->get('sub_institute_id'))
            ->pluck("ExamTitle", "Id")->toArray();
        $data['exams'] = $exams;
        $data['report_card_status_arr'] = ["Y" => "Yes", "N" => "No"];

        return is_mobile($type, "result/grade_subject_wise_master/edit_exam", $data, "view");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $eroor = false;
        $con_points = "";
        $error_reson = "";
        $data = exam_creation::where([
            'syear'            => session()->get('syear'),
            'sub_institute_id' => session()->get('sub_institute_id'),
            'term_id'          => $request->get('term'),
            'exam_id'          => $request->get('exam_id'),
            'standard_id'      => $request->get('standard'),
            'subject_id'       => $request->get('subject'),
            'co_id'            => $request->get('co_id'),
            'title'            => $request->get('title'),
        ])->get()->toArray();

        if (count($data) > 1) {
            $eroor = true;
            $error_reson = "Given Standard Have Exams.";
        } else {
            $data = exam_creation::where([
                    'syear'            => session()->get('syear'),
                    'sub_institute_id' => session()->get('sub_institute_id'),
                    'term_id'          => $request->get('term'),
                    'exam_id'          => $request->get('exam_id'),
                    'standard_id'      => $request->get('standard'),
                    'subject_id'       => $request->get('subject'),
                    'co_id'            => $request->get('co_id'),
                ])
                ->get()->toArray();
            // if (count($data)) {
            //     foreach ($data as $id1 => $arr) {
            //         $con_points = $arr['con_point'];
            //     }
            // }
        }
        if ($eroor == false) {
            $error_co_point = false;
            // if ($request->get('con_point') != '') {
            //     if ($con_points != "") {
            //         if ($con_points != $request->get('con_point')) {
            //             $error_reson = "Convert Point Is Not Matching With Other Exam.";
            //             $error_co_point = true;
            //         }
            //     }
            // }
            $data = [
                'syear'              => session()->get('syear'),
                'sub_institute_id'   => session()->get('sub_institute_id'),
                'term_id'            => $request->get('term'),
                'medium'             => $request->get('medium'),
                'exam_id'            => $request->get('exam_id'),
                'standard_id'        => $request->get('standard'),
                'app_disp_status'    => $request->get('app_disp_status'),
                'subject_id'         => $request->get('subject'),
                'co_id'              => $request->get('co_id'),
                'title'              => $request->get('title'),
                'points'             => $request->get('points'),
                'con_point'          => $request->get('con_point'),
                'marks_type'         => $request->get('marks_type'),
                'report_card_status' => $request->get('report_card_status'),
                'sort_order'         => $request->get('sort_order'),
                'exam_date'          => date("Y-m-d", strtotime($request->get('exam_date'))),
                'updated_by'         => session()->get('user_id'),
                'updated_at'         => now()
            ];


            exam_creation::where(["id" => $id])->update($data);
        }
        if ($eroor || $error_co_point) {
            $res = [
                "status_code" => 0,
                "message"     => $error_reson,
            ];
        } else {
            $res = [
                "status_code" => 1,
                "message"     => "Data Saved",
            ];
        }

        $type = $request->input('type');

        return is_mobile($type, "grade_subject_wise_master.index", $res, "redirect");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        $type = $request->input('type');
        exam_creation::where(["id" => $id])->delete();
        $res = [
            "status_code" => 1,
            "message"     => "Data Deleted",
        ];

        return is_mobile($type, "grade_subject_wise_master.index", $res, "redirect");
    }
}