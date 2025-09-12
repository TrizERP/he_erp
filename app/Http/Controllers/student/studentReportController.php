<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;
use function App\Helpers\get_string;
use function App\Helpers\getStudents;
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
        $tblcustom_fields['house'] = get_string('house','request');
        $tblcustom_fields['amount'] = 'Amount';
        $tblcustom_fields['van'] = 'Van(Shift Wise)';
        $tblcustom_fields['distance'] = 'Distance';
        $tblcustom_fields['optional_subjects'] = 'Optional Subjects';        
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
            $extra_order_by = 'CONVERT(tblstudent.enrollment_no, SIGNED)';
        } elseif ($order_by != '' && $order_by == 'roll_no') {
            $extra_order_by = 'CAST(tblstudent.roll_no AS INT)';
        } else {
            $extra_order_by = 'tblstudent.first_name';
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

        $student_data = DB::table('tblstudent')
            ->select(DB::raw(implode(',', $array)))
            ->join('tblstudent_enrollment', 'tblstudent.id', '=', 'tblstudent_enrollment.student_id')
            ->join('academic_section', 'academic_section.id', '=', 'tblstudent_enrollment.grade_id')
            ->join('standard',function($join) use($marking_period_id){
                $join->on( 'standard.id', '=', 'tblstudent_enrollment.standard_id');
                // ->when($marking_period_id,function($query) use($marking_period_id){
                //     $query->where('standard.marking_period_id',$marking_period_id);
                // });
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
                $q->whereRaw('tblstudent.roll_no ='.$request->stu_roll_no);
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

        $feesCollect = DB::table('fees_collect as fc')
        ->join('tblstudent_enrollment as se',function($q) use($sub_institute_id){
            $q->on('se.student_id','=','fc.student_id')->on('se.syear','=','fc.syear')->on('se.standard_id','=','fc.standard_id')
            ->where('se.sub_institute_id',$sub_institute_id);
        })
        ->join('standard as std','std.id','=','fc.standard_id')
        ->leftjoin('tbluser as u','u.id','=','fc.created_by')
        ->selectRaw('fc.*,sum(fc.amount) as fees_paid,std.name as stdName,CONCAT_WS(" ",COALESCE(u.first_name,"-"),COALESCE(u.middle_name,"-"),COALESCE(u.last_name,"-")) as received_by')
        ->where(['fc.sub_institute_id'=>$sub_institute_id,'fc.student_id'=>$student_id])
        ->where('is_deleted','!=','Y')
        ->groupBy('fc.receipt_no')
        ->get()->toArray();

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
        $enrollNo = $rollNo = $studentImg = 'N/A'; 

        $personalDatas = $parentData = $academicData = [];

        if(isset($personalData[$student_id])){
            $pData  = $personalData[$student_id];
            $enrollNo =isset($pData['image']) ? $pData['image'] : 'N/A'; isset($pData['image']) ? $pData['image'] : 'N/A';
            $rollNo = isset($pData['enrollment_no']) ? $pData['enrollment_no'] : 'N/A';
            $studentImg = isset($pData['roll_no']) ? $pData['roll_no'] : 'N/A';
            //personal data
            $personalDatas = [
                'Name' => isset($pData['student_full_name']) ? $pData['student_full_name'] : 'N/A',
                'Gender' => isset($pData['gender']) ? $pData['gender'] : 'N/A',
                'Religion' => isset($pData['religion_name']) ? $pData['religion_name'] : 'N/A',
                'Caste' => isset($pData['caste_name']) ? $pData['caste_name'] : 'N/A',
                'Sub Caste' => isset($pData['subcast']) ? $pData['subcast'] : 'N/A',
                'Birth Date' => isset($pData['dob']) ? $pData['dob'] : 'N/A',
                'Email' => isset($pData['email']) ? $pData['email'] : 'N/A',
                'Alternativ Email' => 'N/A',
                'Address' => isset($pData['address']) ? $pData['address'] : 'N/A',
                'Land Line' => 'N/A',
                'Home/Hostel Phone' => 'N/A',
                'Mobile Number' => isset($pData['mobile']) ? $pData['student_mobile'] : 'N/A',
                'Blood Group' => isset($bloodGroup[$pData['bloodgroup']]) ? $bloodGroup[$pData['bloodgroup']] : 'N/A',
                'Reserve Category' => isset($pData['reserve_categorey']) ? $pData['reserve_categorey'] : 'NO',
                'Is Physically Handicapped' => isset($pData['disability_if_any']) ? $pData['disability_if_any'] : 'NO',
                'Economy Backward' => isset($pData['economy_backward']) ? $pData['economy_backward'] : 'NO',
            ];
            // parent data 
            $parentData = [
                'Father Name :'=> isset($pData['father_name']) ? $pData['father_name']: 'N/A',
                'Father Mobile :'=> isset($pData['father_mobile']) ? $pData['father_mobile']: 'N/A',
                ];
            if(isset($pData['mother_name'])){
                $parentData['Mother Name :'] = $pData['mother_name'];
            }
            if(isset($pData['mother_mobile'])){
                $parentData['Mother Mobile :'] = $pData['mother_mobile'];
            }
            // current academic data 
            $academicData = [
                'academic_section'=> isset($pData['academic_section']) ? $pData['academic_section'] : 'N/A',
                'branch'=>isset($pData['standard_name'])  ? $pData['standard_name']  : 'N/A',
                'division'=> isset($pData['division_name']) ?$pData['division_name']  : 'N/A',
                'student_quota'=> isset($pData['student_quota']) ?$pData['student_quota'] : 'N/A',
                'admission_year'=> isset($pData['admission_year']) ? $pData['admission_year'] : 'N/A',
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
        $res['feesDetails'] = $feesCollect;
        $res['cancelFeesCollect'] =$cancelFeesCollect;
        $res['otherFeesDetails'] =$otherFeesDetails;
        return is_mobile($type, "student/show_student_report_model", $res, "view");
    }
}
