<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;
use function App\Helpers\get_string;
use function App\Helpers\getStudents;
use function App\Helpers\FeeMonthId;
use App\Http\Controllers\student\tblstudentController;

class studentReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        // $tblcustom_fields = DB::table("tblcustom_fields")
        // ->where(["sub_institute_id" => session()->get('sub_institute_id'),"table_name" => "tblstudent"])
        // ->pluck("field_label", "field_name");

        // $tblcustom_fields['enrollment_no'] = 'Enrollment No';
        // $tblcustom_fields['first_name'] = 'First Name';
        // $tblcustom_fields['middle_name'] = 'Middle Name';
        // $tblcustom_fields['last_name'] = 'Last Name';
        // $tblcustom_fields['father_name'] = 'Father Name';
        // $tblcustom_fields['['mother_name']'] = 'Mother Name';
        // $tblcustom_fields['gender'] = 'Gender';
        // $tblcustom_fields['dob'] = 'Birthdate';
        // $tblcustom_fields['mobile'] = 'Mobile';
        // $tblcustom_fields['mother_mobile'] = 'Mother Mobile';
        // $tblcustom_fields['email'] = 'Email';
        // $tblcustom_fields['username'] = 'Username';
        // $tblcustom_fields['admission_year'] = 'Admission Year';
        // $tblcustom_fields['admission_date'] = 'Admission Date';
        // $tblcustom_fields['city'] = 'City';
        // $tblcustom_fields['state'] = 'State';
        // $tblcustom_fields['address'] = 'Address';
        // $tblcustom_fields['pincode'] = 'Pincode';

        $tblcustom_fields = $this->customFields($request);
        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $tblcustom_fields;

        return is_mobile($type, "student/show_student_report", $res, "view");
    }

    public function bulkIndex(Request $request)
    {
        $type = $request->input('type');
        $tblcustom_fields = $this->customFields($request);

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $tblcustom_fields;

        return is_mobile($type, "student/bulk_student_update", $res, "view");
    }

    public function customFields(Request $request)
    {
        $sub_institute_id = $request->session()->get('sub_institute_id');
        //$tblcustom_fields['enrollment_no'] = get_string('grno','request');
        //$tblcustom_fields['student_name'] = 'Student Name';
        $tblcustom_fields['roll_no'] = 'Roll No';
        $tblcustom_fields['first_name'] = 'First Name';
        $tblcustom_fields['middle_name'] = 'Middle Name';
        $tblcustom_fields['last_name'] = 'Surname';
        $tblcustom_fields['dob'] = 'Birthdate';
        $tblcustom_fields['mobile'] = 'Mobile';
        $tblcustom_fields['address'] = 'Address';
        $tblcustom_fields['city'] = 'City';
        $tblcustom_fields['state'] = 'State';
        $tblcustom_fields['pincode'] = 'Pincode';        
        $tblcustom_fields['student_mobile'] = get_string('studentmobile','request');
        $tblcustom_fields['mother_mobile'] = 'Mother Mobile';
        $tblcustom_fields['father_name'] = 'Father Name';
        $tblcustom_fields['mother_name'] = 'Mother Name';
        $tblcustom_fields['gender'] = 'Gender';
        $tblcustom_fields['studentbatch'] = 'Batch';
        $tblcustom_fields['email'] = 'Email';
        $tblcustom_fields['username'] = 'Username';
        $tblcustom_fields['uniqueid'] = get_string('uniqueid','request');
        $tblcustom_fields['admission_year'] = 'Admission Year';
        $tblcustom_fields['admission_date'] = 'Admission Date';
        $tblcustom_fields['religion'] = 'Religion';
        $tblcustom_fields['student_quota'] = 'Student Quota';
        $tblcustom_fields['cast'] = 'Caste';
        $tblcustom_fields['subcast'] = 'Subcaste';
        $tblcustom_fields['bloodgroup'] = 'Blood Group';
        $tblcustom_fields['adharnumber'] = 'Adhar Number';
        $tblcustom_fields['anuualincome'] = get_string('anuualincome','request');
        $tblcustom_fields['image'] = 'Image';
        //$tblcustom_fields['house'] = get_string('house','request');
        //$tblcustom_fields['amount'] = 'Amount';
        //$tblcustom_fields['van'] = 'Van(Shift Wise)';
        //$tblcustom_fields['distance'] = 'Distance';
        //$tblcustom_fields['optional_subjects'] = 'Optional Subjects';        
        $tblcustom_fields['nationality'] = get_string('nationality','request');
        $tblcustom_fields['place_of_birth'] = get_string('birthplace','request');

        $tblcustoms = DB::table("tblcustom_fields")
            ->where(["status" => "1", "table_name" => "tblstudent"])
            ->whereRaw('(sub_institute_id = '.$sub_institute_id.' OR common_to_all = 1)')
            ->pluck("field_label", "field_name");

        $customfieldArray = [];
        foreach ($tblcustoms as $key => $value) {
            $customfieldArray[$key] = $value;
        }

        return array_merge($tblcustom_fields, $customfieldArray);
    }

    public function searchStudent(Request $request)
    {
        $grade_id = $request->input("grade");
        $standard_id = $request->input("standard");
        $division_id = $request->input("division");
        $order_by = $request->input("order_by");
        $page = $request->input("page");
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $syear = $request->session()->get('syear');
        $marking_period_id=session()->get('term_id');

        $extra_order_by = '';
        $extraSearchArray = [];
        $extraSearchArray['tblstudent_enrollment.sub_institute_id'] = $sub_institute_id;
        $extraSearchArray['tblstudent_enrollment.syear'] = $syear;
        $extraSearchArray['tblstudent.status'] = 1;
        if ($grade_id != '') {
            $extraSearchArray['tblstudent_enrollment.grade_id'] = $grade_id;
        }
        if ($standard_id != '') {
            $extraSearchArray['tblstudent_enrollment.standard_id'] = $standard_id;
        }
        if ($division_id != '') {
            $extraSearchArray['tblstudent_enrollment.section_id'] = $division_id;
        }

        if ($order_by != '' && $order_by == 'student_name') {
            $extra_order_by = 'tblstudent.first_name';
        } elseif ($order_by != '' && $order_by == 'standard_id') {
            $extra_order_by = 'standard.sort_order';
        } elseif ($order_by != '' && $order_by == 'enrollment_no') {
            $extra_order_by = "CAST(REGEXP_SUBSTR(enrollment_no, '[0-9]+') AS UNSIGNED)";
        } elseif ($order_by != '' && $order_by == 'roll_no') {
            $extra_order_by = 'CAST(tblstudent_enrollment.roll_no AS INT)';
        } else {
            $extra_order_by = "CAST(REGEXP_SUBSTR(enrollment_no, '[0-9]+') AS UNSIGNED)";
        }


        $array = [
            'tblstudent.enrollment_no as enrollment_no', 'tblstudent.id as id', 'standard.name as standard', 'division.name as division',// 'academic_section.title as grade',
        ];
        $header = [
            'enrollment_no' => get_string('grno','request'), 'student_name' => 'Student Name', 'standard' => get_string('standard','request'), 'division' => get_string('division','request'),// 'grade' => get_string('academicsection','request'),
            
        ];//,'id' => 'Stu_ID'

        $searchArr = ['_'];
        $replaceArr = [' '];

        if ($request->input('dynamicFields') == '') {
            $array = [
                'tblstudent.enrollment_no as enrollment_no', 'tblstudent.id as id', 'standard.name as standard', 'division.name as division',// 'academic_section.title as grade',
            ];
            $header = [
                'enrollment_no' => get_string('grno','request'), 'student_name' => 'Student Name', 'standard' => get_string('standard','request'), 'division' => get_string('division','request'),// 'grade' => get_string('academicsection','request'),
                
            ];//'id' => 'Stu_ID',
            // $res['status_code'] = 0;
            // $res['message'] = "Please select one checkbox atlease to view report";
            // return is_mobile($type, "student_report.index", $res);
        } else {
            $searchArr1 = ['first_name', 'last_name', 'place_of_birth', 'student_mobile','optional_subjects'];
            $replaceArr1 = ['First Name', 'Surname', get_string('birthplace','request'), get_string('studentmobile','request'),'Optional Subjects'];

            foreach ($request->input('dynamicFields') as $key => $value) {
                if ($value != "bloodgroup" && $value != "van" && $value != "optional_subjects") {
                    $array[] = $value;
                }
                
                $value1 = str_replace($searchArr1, $replaceArr1, $value);
                $value2 = str_replace($searchArr, $replaceArr, $value1);

                $header[$value] = ucfirst($value2);
            }

            $array[] = 'religion.religion_name as religion';
            $array[] = 'house_master.house_name as house';
            $array[] = 'student_quota.title as student_quota';
            $array[] = 'caste.caste_name as cast';
            $array[] = 'blood_group.bloodgroup as bloodgroup';
            $array[] = 'CONCAT(transport_vehicle.vehicle_number, " (", transport_school_shift.shift_title, ")") as van';
            $array[] = 'tblstudent.place_of_birth as place_of_birth';
            $array[] = 'tblstudent.student_mobile as studentmobile';
            $array[] = 'GROUP_CONCAT(IFNULL(subject.subject_name, "-")) as optional_subjects';
            $array[] = 'batch.title as studentbatch';
        }
        $array[] = 'concat_ws(" ",tblstudent.first_name,tblstudent.middle_name,tblstudent.last_name) AS student_name';

        // Add attendance-related calculations if requested
        $dynamicFields = $request->input('dynamicFields') ?? [];

        $student_data = DB::table('tblstudent')
            ->select(DB::raw(implode(',', $array)))
            ->join('tblstudent_enrollment', 'tblstudent.id', '=', 'tblstudent_enrollment.student_id')
            ->join('academic_section', 'academic_section.id', '=', 'tblstudent_enrollment.grade_id')
            ->join('standard',function($join) use($marking_period_id){
                $join->on( 'standard.id', '=', 'tblstudent_enrollment.standard_id')
                 ->when($marking_period_id,function($query) use($marking_period_id){
                     $query->where('standard.marking_period_id',$marking_period_id);
                 });
            })
            ->join('division', 'division.id', '=', 'tblstudent_enrollment.section_id')
            ->leftjoin('religion', 'religion.id', '=', 'tblstudent.religion')
            ->leftjoin('house_master', 'house_master.id', '=', 'tblstudent_enrollment.house_id')
            ->leftjoin('student_quota', 'student_quota.id', '=', 'tblstudent_enrollment.student_quota')
            ->leftjoin('caste', 'caste.id', '=', 'tblstudent.cast')
            ->leftjoin('blood_group', 'blood_group.id', '=', 'tblstudent.bloodgroup')
            ->leftjoin('batch', 'tblstudent.studentbatch', '=', 'batch.id')
            ->leftjoin('transport_map_student', 'transport_map_student.student_id', '=', 'tblstudent.id')
            //->leftjoin('transport_vehicle', 'transport_vehicle.id', '=', 'transport_map_student.from_bus_id')
            ->leftjoin('transport_vehicle', function($join) {
                $join->on('transport_vehicle.id', '=', 'transport_map_student.from_bus_id')
                     ->where('transport_vehicle.sub_institute_id', '=', DB::raw('tblstudent_enrollment.sub_institute_id'));
            })
            ->leftjoin('student_optional_subject',function($join){
                $join->on('student_optional_subject.student_id', '=', 'tblstudent.id')->where('student_optional_subject.syear',session()->get('syear'));
            })
            ->leftjoin('subject', 'student_optional_subject.subject_id', '=', 'subject.id')
            ->leftJoin('transport_school_shift', 'transport_vehicle.school_shift', '=', 'transport_school_shift.id')
            ->where($extraSearchArray)
            ->when($request->student_status==0,function($q) use ($request) {
                $q->whereRaw('tblstudent_enrollment.end_date is NULL');
            },function($q) use ($request) {
                $q->whereRaw('tblstudent_enrollment.end_date is NOT NULL');
            })
            ->when($request->stu_first_name,function($q) use ($request) {
                $q->whereRaw('tblstudent.first_name like "%'.$request->stu_first_name.'%"');
            })
            ->when($request->stu_last_name,function($q) use ($request) {
                $q->whereRaw('tblstudent.last_name like "%'.$request->stu_last_name.'%"');
            })
            ->when($request->stu_enrollment_no,function($q) use ($request) {
                $q->whereRaw('tblstudent.enrollment_no = "'.$request->stu_enrollment_no.'"');
            })
            ->when($request->stu_roll_no,function($q) use ($request) {
                $q->whereRaw('tblstudent_enrollment.roll_no ='.$request->stu_roll_no);
            })
            ->orderByRaw($extra_order_by)
            ->groupBy('tblstudent.id')
            ->get();

        $res['status_code'] = 1;
        $res['message'] = "Student List";
        $res['student_data'] = $student_data;
        $res['grade_id'] = $grade_id;
        $res['standard_id'] = $standard_id;
        $res['division_id'] = $division_id;
        $res['data'] = $this->customFields($request);
        $res['headers'] = $header;
        $res['activeStatus'] = $request->student_status;
        $res['stu_first_name'] = $request->stu_first_name;
        $res['stu_last_name'] = $request->stu_last_name;
        $res['stu_enrollment_no'] = $request->stu_enrollment_no;
        $res['stu_roll_no'] = $request->stu_roll_no;

        return is_mobile($type, "student/show_student_report", $res, "view");

    }

    public function underDevelopment()
    {
        return view("under_development");
    }

    public function firstpage_school()
    {
        return view("firstpage_school");
    }

    public function firstpage_student()
    {
        return view("firstpage_student");
    }

    public function firstpage_teacher()
    {
        return view("firstpage_teacher");
    }

    public function studentProfileData(Request $request){
        $type = $request->type;
        $student_id = $request->student_id;
        if ($type == "API") {
            $sub_institute_id = $request->input('sub_institute_id');
            $syear = $request->input('syear');
        } else {
            $sub_institute_id = $request->session()->get('sub_institute_id');
            $syear = session()->get('syear');
		}

        // get personal details
        $stu_arr[] = $student_id;
        $personalData = getStudents($stu_arr,$sub_institute_id,$syear);
        
        $bloodGroup = DB::table('blood_group')->pluck('bloodgroup','id')->toArray();

        $pastEducation = DB::table('tblstudent_past_education')->where(['sub_institute_id'=>$sub_institute_id,'student_id'=>$student_id])->get()->toArray();

        $certificates = DB::table('certificate_history as ch')
        ->join('tblstudent_enrollment as se',function($q) use($sub_institute_id){
            $q->on('se.student_id','=','ch.student_id')->on('se.syear','=','ch.syear')
            ->where('se.sub_institute_id',$sub_institute_id);
        })
        ->join('standard as std','std.id','=','se.standard_id')
        ->selectRaw('ch.*,std.name as stdName')
        ->where(['ch.sub_institute_id'=>$sub_institute_id,'ch.student_id'=>$student_id])->get()->toArray();

        // Get fees data using the same logic as feesReportController
        $feesData = $this->getStudentFeesData($student_id, $syear, $sub_institute_id);
        
        // Get cancel fees (still need separate query for cancellation details)
        $cancelFeesCollect = DB::table('fees_cancel as fc')
        ->join('tblstudent as ts', function ($join) {
            $join->whereRaw('ts.id = fc.student_id AND ts.sub_institute_id = fc.sub_institute_id');
        })->join('tblstudent_enrollment as te', function ($join) {
            $join->whereRaw('te.student_id = ts.id AND te.syear = fc.syear');
        })->join('student_quota as sq', function ($join) {
            $join->whereRaw('sq.id = te.student_quota AND ts.sub_institute_id = sq.sub_institute_id');
        })->join('standard as s', function ($join){
            $join->whereRaw('s.id = te.standard_id');
        })->join('tbluser as u', function ($join) {
            $join->whereRaw('u.id = fc.cancelled_by')->where('u.status',1); 
        })
        ->leftjoin('fees_collect as f','f.receipt_no','=','fc.reciept_id')
        ->selectRaw("fc.id,fc.reciept_id,ts.enrollment_no, CONCAT_WS(' ',ts.first_name,ts.middle_name,ts.last_name)
            AS student_name,ts.admission_year,te.student_quota,s.name as std_name,fc.amountpaid,fc.cancel_type,
            fc.cancel_remark, DATE_FORMAT(fc.cancel_date,'%d-%m-%Y') AS cancel_date, CONCAT_WS(' ',u.first_name,u.middle_name,
            u.last_name) AS cancelled_by,sq.title as student_quota_name,f.payment_mode,f.bank_name,f.cheque_no,f.cheque_bank_name,f.bank_branch")
        // ->where('te.syear', $syear)
        ->where(['fc.sub_institute_id'=>$sub_institute_id,'fc.student_id'=>$student_id])
        ->get()->toArray();
        
        // Get other fees details
        $otherFeesDetails = DB::table('fees_other_collection as c')
            ->join('fees_other_head as h', function ($join) {
                $join->whereRaw('c.deduction_head_id = h.id');
            })->join('tblstudent_enrollment as se', function ($join) {
                $join->whereRaw('se.student_id = c.student_id AND se.syear = c.syear AND se.end_date is null AND c.standard_id = se.standard_id');
            })->join('standard as st', function ($join){
                $join->whereRaw('st.id = se.standard_id');
            })
            ->leftjoin('tbluser as u','u.id','=','c.created_by')
            ->selectRaw("h.display_name as fees_head,h.amount AS total_amt, c.deduction_amount,c.deduction_remarks,c.deduction_date,c.payment_mode,c.bank_name,c.cheque_dd_no,c.receipt_id,c.id,c.student_id,st.name as stdName,CONCAT_WS(' ',COALESCE(u.first_name,'-'),COALESCE(u.middle_name,'-'),COALESCE(u.last_name,'-')) as received_by")
            ->where('c.sub_institute_id', $sub_institute_id)
            ->where('c.student_id',$student_id)
            // ->where('c.syear', $syear)
            ->where('c.is_deleted', '=', 'N')
            ->orderBy('c.deduction_date')
            ->get()->toArray();
        
        // get start date att
       
        // echo "<pre>";print_r($otherFeesDetails);exit;
        $enrollNo = $rollNo = $studentImg = '-'; 

        $personalDatas = $parentData = $academicData = [];

        if(isset($personalData[$student_id])){
            $pData  = $personalData[$student_id];
            $studentImg =isset($pData['image']) ? $pData['image'] : '-';
            $enrollNo = isset($pData['enrollment_no']) ? $pData['enrollment_no'] : '-';
            $rollNo = isset($pData['roll_no']) ? $pData['roll_no'] : '-';
            //personal data
            $personalDatas = [
                'Name' => isset($pData['student_full_name']) ? $pData['student_full_name'] : '-',
                'Gender' => isset($pData['gender']) ? $pData['gender'] : '-',
                'Religion' => isset($pData['religion_name']) ? $pData['religion_name'] : '-',
                'Caste' => isset($pData['caste_name']) ? $pData['caste_name'] : '-',
                'Sub Caste' => isset($pData['subcast']) ? $pData['subcast'] : '-',
                'Birth Date' => isset($pData['dob']) ? $pData['dob'] : '-',
                'Email' => isset($pData['email']) ? $pData['email'] : '-',
                'Alternate Email' => isset($pData['alternate_email']) ? $pData['alternate_email'] : '-',
                'Address' => isset($pData['address']) ? $pData['address'] : '-',
                //'Land Line' => '-',
                //'Home/Hostel Phone' => '-',
                'Student Mobile' => isset($pData['mobile']) ? $pData['student_mobile'] : '-',
                'Blood Group' => isset($bloodGroup[$pData['bloodgroup']]) ? $bloodGroup[$pData['bloodgroup']] : '-',
                'Reserve Category' => isset($pData['reserve_categorey']) ? $pData['reserve_categorey'] : '-',
                'Is Physically Handicapped' => isset($pData['disability_if_any']) ? $pData['disability_if_any'] : '-',
                'Economy Backward' => isset($pData['economy_backward']) ? $pData['economy_backward'] : '-',
            ];
            // parent data 
            $parentData = [
                'Father Name :'=> isset($pData['father_name']) ? $pData['father_name']: '-',
                'Father Mobile :'=> isset($pData['father_mobile']) ? $pData['father_mobile']: '-',
                ];
            if(isset($pData['mother_name'])){
                $parentData['Mother Name :'] = $pData['mother_name'];
            }
            if(isset($pData['mother_mobile'])){
                $parentData['Mother Mobile :'] = $pData['mother_mobile'];
            }
            // current academic data 
            $academicData = [
                'academic_section'=> isset($pData['academic_section']) ? $pData['academic_section'] : '-',
                'branch'=>isset($pData['standard_name'])  ? $pData['standard_name']  : '-',
                'division'=> isset($pData['division_name']) ?$pData['division_name']  : '-',
                'student_quota'=> isset($pData['student_quota']) ?$pData['student_quota'] : '-',
                'admission_year'=> isset($pData['admission_year']) ? $pData['admission_year'] : '-',
                ];
        }
        // echo "<pre>";print_r($feesCollect);exit;
        $res['enrollment_no'] =$enrollNo;
        $res['roll_no'] =$rollNo;
        $res['student_image'] =$studentImg;
        $res['personalData'] =$personalDatas;
        $res['parentData'] = $parentData;
        $res['academicData'] = $academicData;
        $res['pastEducation'] = $pastEducation;
        $res['issuedCertificates'] = $certificates;
        $res['feesDetails'] = $feesData;
        $res['cancelFeesCollect'] =$cancelFeesCollect;
        $res['otherFeesDetails'] =$otherFeesDetails;
        return is_mobile($type, "student/show_student_report_model", $res, "view");
    }

    /**
     * Get student fees data using the same logic as feesReportController
     * This avoids running duplicate queries for fees data
     *
     * @param int $student_id
     * @param int $syear
     * @param int $sub_institute_id
     * @return array
     */
    private function getStudentFeesData($student_id, $syear, $sub_institute_id)
    {
        $marking_period_id = session()->get('term_id');
        
        $extra_fp = " AND fp.syear = '" . $syear . "' AND fp.sub_institute_id = '" . $sub_institute_id . "' AND fp.is_deleted = 'N' ";
        $extra_fo = " AND fo.syear = '" . $syear . "' AND fo.sub_institute_id = '" . $sub_institute_id . "' AND fo.is_deleted = 'N' ";
        
        // Add student filter
        $extra_fp .= " AND t.id = '" . $student_id . "'";
        $extra_fo .= " AND t.id = '" . $student_id . "'";

        $data = DB::table(function ($query) use ($sub_institute_id, $syear, $extra_fo, $extra_fp, $marking_period_id) {
            $query->selectRaw('t.id as student_id, t.enrollment_no, te.roll_no, t.uniqueid, t.place_of_birth, '
                . DB::raw("CONCAT_WS(' ', t.first_name, t.middle_name, t.last_name) as student_name") . ', g.title as grade, s.name as standard_name, d.name as division_name, fp.created_date, '
                . DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) AS user_name, fp.term_id, fp.receiptdate, fp.receipt_no, fp.payment_mode, '
                . 'fp.cheque_bank_name, fp.bank_branch, fp.cheque_no, fp.cheque_date, b.title as batch, sq.title as quota, fp.remarks, '
                . 'IFNULL(fp.amount, 0) AS actual_amountpaid'))
                ->from('tblstudent as t')
                ->join('tblstudent_enrollment as te', function ($join) use($syear){
                    $join->on('te.student_id', '=', 't.id')->where('te.syear',$syear);
                })
                ->leftJoin('academic_section as g', 'g.id', '=', 'te.grade_id')
                ->Join('standard as s',function($q) use($marking_period_id) {
                    $q->on('s.id', '=', 'te.standard_id');
                })
                ->leftJoin('division as d', 'd.id', '=', 'te.section_id')
                ->leftJoin('student_quota as sq', 'sq.id', '=', 'te.student_quota')
                ->leftjoin('batch as b', function ($join) {
                    $join->on('b.standard_id', '=', 'te.standard_id')
                        ->whereRaw('b.division_id = te.section_id')
                        ->whereRaw('b.id = t.studentbatch')
                        ->whereRaw('b.syear = te.syear');
                })
                ->join('fees_collect as fp', function($join) {
                    $join->on('fp.student_id', '=', 'te.student_id')
                         ->on('fp.standard_id', '=', 'te.standard_id');
                })
                ->leftJoin('tbluser as u', 'fp.created_by', '=', 'u.id')
                ->whereRaw("1=1 " . $extra_fp)

                ->unionAll(function ($query) use ($sub_institute_id, $syear, $extra_fo, $extra_fp, $marking_period_id) {
                    $query->selectRaw('t.id as student_id, t.enrollment_no, te.roll_no, t.uniqueid, t.place_of_birth, '
                        . DB::raw("CONCAT_WS(' ', t.first_name, t.middle_name, t.last_name) as student_name") . ', g.title as grade, s.name as standard_name, d.name as division_name, NULL AS created_date, '
                        . DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) AS user_name, fo.month_id AS term_id, fo.receiptdate AS receiptdate, fo.reciept_id AS receipt_no, fo.payment_mode AS payment_mode, '
                        . 'fo.bank_name as cheque_bank_name, fo.bank_branch, fo.cheque_dd_no as cheque_no, fo.cheque_dd_date AS cheque_date, b.title as batch, sq.title as quota, NULL as remarks, '
                        . 'IFNULL(fo.actual_amountpaid, 0) AS actual_amountpaid'))->from('tblstudent as t')
                        ->join('tblstudent_enrollment as te', function ($join) use($syear){
                            $join->on('te.student_id', '=', 't.id')->where('te.syear',$syear);
                        })
                        ->leftJoin('academic_section as g', 'g.id', '=', 'te.grade_id')
                        ->Join('standard as s',function($q) use($marking_period_id) {
                            $q->on('s.id', '=', 'te.standard_id')->where('s.marking_period_id',$marking_period_id);
                        })
                        ->leftJoin('division as d', 'd.id', '=', 'te.section_id')
                        ->leftJoin('student_quota as sq', 'sq.id', '=', 'te.student_quota')
                        ->leftjoin('batch as b', function ($join) {
                            $join->on('b.standard_id', '=', 'te.standard_id')
                                ->whereRaw('b.division_id = te.section_id')
                                ->whereRaw('b.id = t.studentbatch')
                                ->whereRaw('b.syear = te.syear');
                        })
                        ->leftJoin('fees_paid_other as fo', 'fo.student_id', '=', 'te.student_id')
                        ->leftJoin('tbluser as u', 'fo.created_by', '=', 'u.id')
                        ->whereRaw("1=1 " . $extra_fo);
                });
        })
            ->selectRaw('student_id, enrollment_no, roll_no, uniqueid, place_of_birth, student_name, grade, standard_name, division_name, created_date, user_name, GROUP_CONCAT(term_id) AS term_ids, receiptdate, receipt_no, payment_mode, cheque_bank_name, bank_branch, cheque_no, cheque_date, batch, quota, remarks, SUM(IFNULL(actual_amountpaid, 0)) AS actual_amountpaid')
            ->groupBy(['student_id', 'receipt_no', 'receiptdate', 'payment_mode', 'cheque_no']);
            
        $data = $data->get()->toArray();
        $feesData = json_decode(json_encode($data), true);
        
        // Format the fees data to match the view expectations
        $formattedFees = [];
        foreach ($feesData as $fee) {
            $formattedFees[] = (object)[
                'stdName' => $fee['standard_name'] ?? '-',
                'receipt_no' => $fee['receipt_no'] ?? '-',
                'receiptdate' => $fee['receiptdate'] ?? '-',
                'payment_mode' => $fee['payment_mode'] ?? '-',
                'cheque_no' => $fee['cheque_no'] ?? '-',
                'cheque_bank_name' => $fee['cheque_bank_name'] ?? '-',
                'bank_branch' => $fee['bank_branch'] ?? '-',
                'fees_paid' => $fee['actual_amountpaid'] ?? 0,
                'received_by' => $fee['user_name'] ?? '-'
            ];
        }
        
        return $formattedFees;
    }
}
