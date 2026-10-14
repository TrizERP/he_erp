<?php

namespace App\Http\Controllers\admission;

use App\Http\Controllers\Controller;
use App\Models\admission\admissionEnquiryModel;
use App\Models\admission\admissionRegistrationModel;
use App\Models\school_setup\bloodgroupModel;
use App\Models\school_setup\standardModel;
use App\Models\settings\tblcustomfieldsModel;
use App\Models\settings\tblfields_dataModel;
use App\Models\student\studentQuotaModel;
use App\Services\Admission\AdmissionAccess;
use App\Services\Admission\AdmissionException;
use App\Services\Admission\ConfirmationService;
use App\Services\Admission\NumberService;
use App\Models\student\tblstudentEnrollmentModel;
use App\Models\student\tblstudentModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;
use GenTux\Jwt\GetsJwtToken;

class admissionRegistrationController extends Controller
{
    use GetsJwtToken;
    
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get("sub_institute_id");
        $syear = session()->get("syear");
        $marking_period_id = session()->get('term_id');
        if($type=="API"){
            try {
                if (!$this->jwtToken()->validate()) {
                    $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];
    
                    return response()->json($response, 401);
                }
            } catch (\Exception $e) {
                $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];
    
                return response()->json($response, 401);
            }
            $sub_institute_id = $request->get('sub_institute_id');
            $syear = $request->get('syear');            
        }

        $data = DB::table('admission_enquiry as ae')
            ->join('admission_form as af', function ($join) {
                $join->whereRaw('ae.id = af.enquiry_id');
            })->leftJoin('tblstudent as ts', function ($join) {
                $join->whereRaw('ts.admission_id = ae.id AND ts.admission_year = ae.syear AND ts.sub_institute_id = ae.sub_institute_id');
            })->leftJoin('standard as s', function ($join) use($marking_period_id) {
                $join->whereRaw('ts.admission_id = ae.id AND ts.admission_year = ae.syear AND ts.sub_institute_id = ae.sub_institute_id');
                // ->when($marking_period_id,function($query) use ($marking_period_id){
                //     $query->where('s.marking_period_id',$marking_period_id);
                // });
            })
            ->selectRaw("ae.*,COUNT(ts.id) AS total_student_count,ae.remarks AS enquiry_remark,s.name AS std_name")
            ->where('ae.sub_institute_id', $sub_institute_id)
            ->where('ae.syear', $syear)->groupBy('ae.id')->get()->toArray();

        $data = array_map(function ($value) {
            return (array) $value;
        }, $data);

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $data;

        return is_mobile($type, 'admission/registration/show_admission_registration', $res, 'view');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit(Request $request, $id)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $marking_period_id = session()->get('term_id');
        if($type=="API"){
            try {
                if (!$this->jwtToken()->validate()) {
                    $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];
    
                    return response()->json($response, 401);
                }
            } catch (\Exception $e) {
                $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];
    
                return response()->json($response, 401);
            }
            $sub_institute_id = $request->get('sub_institute_id');
            $syear = $request->get('syear');            
        }
        if ($sub_institute_id == 198) // For Mahaeshvari school
        {
            $data = DB::table('admission_enquiry as ae')
                ->join('admission_form as af', function ($join) {
                    $join->whereRaw('ae.id = af.enquiry_id LEFT JOIN admission_registration ar ON ae.id = ar.enquiry_id');
                })
                ->selectRaw("ae.*,ar.*,ae.id as id,ae.enquiry_no as enquiry_no,CONCAT_WS(',',ae.house_no,
                    ae.`building_name_appratment_name_society_name`,ae.district_name,ae.pin_code,ae.state) AS address,
			        ae.previous_standard,ae.mother_name,ae.mobile_number_mother ,ae.place_of_birth,ar.enquiry_id as registration_enquiry_id")
                ->where('ae.id', $id)->get()->toArray();
        } else {
            $data = DB::table('admission_enquiry as ae')
                ->join('admission_form as af', function ($join) {
                    $join->whereRaw('ae.id = af.enquiry_id');
                })->leftJoin('admission_registration as ar', function ($join) {
                    $join->whereRaw('ae.id = ar.enquiry_id');
                })
                ->selectRaw("ae.*,ar.*,ae.id as id,ae.enquiry_no as enquiry_no,ar.enquiry_id as registration_enquiry_id")
                ->where('ae.id', $id)->get()->toArray();
        }

        $data = array_map(function ($value) {
            return (array) $value;
        }, $data);

        $editData = $data;
        $checkStudent = tblstudentModel::where(['admission_id' => $id])->get()->toArray();

        $dataCustomFields = tblcustomfieldsModel::where(['status' => "1", 'table_name' => "admission_registration"])
            ->whereRaw('(sub_institute_id = '.$sub_institute_id.' OR common_to_all = 1)')
            ->get();

        $fieldsData = tblfields_dataModel::get()->toArray();
        $i = 0;
        $finalfieldsData = array();
        foreach ($fieldsData as $key => $value) {
            $finalfieldsData[$value['field_id']][$i]['display_text'] = $value['display_text'];
            $finalfieldsData[$value['field_id']][$i]['display_value'] = $value['display_value'];
            $i++;
        }


        if (count($checkStudent) > 0) {
            $res['display_save_student'] = '0';
        } else {
            $res['display_save_student'] = '1';
        }

        $category = studentQuotaModel::where(['sub_institute_id' => $sub_institute_id])->get()->toArray();

        if (isset($editData[0]['enrollment_no']) && $editData[0]['enrollment_no'] != '') {
            $res['new_enrollment_no'] = $editData[0]['enrollment_no'];
        } else {
            $res['new_enrollment_no'] = $this->max_enrollment_no($sub_institute_id, $editData[0]['admission_standard']);
        }

        $standard = standardModel::where(['sub_institute_id' => $sub_institute_id])->get()->toArray();

        $bloodgroupData = bloodgroupModel::select()->get();

        $getDiv = DB::table('std_div_map as sdm')
            ->join('standard as s', function ($join) use($marking_period_id) {
                $join->whereRaw('s.id =sdm.standard_id AND s.sub_institute_id = sdm.sub_institute_id');
                // ->when($marking_period_id,function($query) use ($marking_period_id){
                //     $query->where('tblstudent.marking_period_id',$marking_period_id);
                // });
            })->join('division as d', function ($join) {
                $join->whereRaw('d.id = sdm.division_id AND d.sub_institute_id = sdm.sub_institute_id');
            })->selectRaw('d.id,d.name,sdm.standard_id')
            ->where('sdm.sub_institute_id', $sub_institute_id)
            ->where('sdm.standard_id', $editData[0]['admission_standard'])->get()->toArray();

        $getDiv = array_map(function ($value) {
            return (array) $value;
        }, $getDiv);

        $res['status_code'] = "1";
        $res['message'] = "Successfully";
        $res['editData'] = $editData['0'];
        $res['standard'] = $standard;
        $res['bloodgroup_data'] = $bloodgroupData;
        $res['custom_fields'] = $dataCustomFields;
        if (count($getDiv) > 0) {
            $res['division'] = $getDiv;
        }
        if (count($finalfieldsData) > 0) {
            $res['data_fields'] = $finalfieldsData;
        }
        if (count($category) > 0) {
            $res['category'] = $category;
        }

        return is_mobile($type, 'admission/registration/edit_admission_registration', $res, 'view');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $type = $request->input("type");
        $sub_institute_id = $request->session()->get("sub_institute_id");
        $user_id = $request->session()->get("user_id");
        if($type=="API"){
            try {
                if (!$this->jwtToken()->validate()) {
                    $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];
    
                    return response()->json($response, 401);
                }
            } catch (\Exception $e) {
                $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];
    
                return response()->json($response, 401);
            }
            $sub_institute_id = $request->get('sub_institute_id');
            $syear = $request->get('syear');   
            $user_id = $request->get('user_id');                                 
        }
        $editdata['first_name'] = $request->input("first_name");
        $editdata['middle_name'] = $request->input("middle_name");
        $editdata['last_name'] = $request->input("last_name");
        $editdata['mobile'] = $request->input("mobile");
        $editdata['email'] = $request->input("email");
        $editdata['date_of_birth'] = $request->input("date_of_birth");
        $editdata['age'] = $request->input("age");
        $editdata['address'] = $request->input("address");
        $editdata['previous_school_name'] = $request->input("previous_school_name");
        $editdata['source_of_enquiry'] = $request->input("source_of_enquiry");

        admissionEnquiryModel::where(['id' => $id, 'sub_institute_id' => $sub_institute_id])->update($editdata);

        $data = $request->except([
            '_method', '_token','token','syear','sub_institute_id','user_id', 'submit', 'type', 'first_name', 'middle_name', 'last_name', 'mobile', 'email',
            'date_of_birth', 'age', 'address', 'previous_school_name', 'previous_standard', 'source_of_enquiry',
            'admission_standard',
        ]); //,'remarks','followup_date'

        $checkForm = admissionRegistrationModel::where(['enquiry_id' => $id])->get()->toArray();
        if (count($checkForm) > 0) {
            $data['enquiry_id'] = $id;
            $data['created_by'] = $user_id;
            $data['created_on'] = date('Y-m-d H:i:s');
            $data['sub_institute_id'] = $sub_institute_id;

            admissionRegistrationModel::where(['enquiry_id' => $id])->update($data);
        } else {
            $data['enquiry_id'] = $id;
            $data['created_by'] = $user_id;
            $data['created_on'] = date('Y-m-d H:i:s');
            $data['sub_institute_id'] = $sub_institute_id;

            admissionRegistrationModel::insert($data);
        }

        $res['status_code'] = "1";
        $res['message'] = "Added successfully";

        return is_mobile($type, "admission_registration.index", $res);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Confirm the admission and create (or link) the student. Delegates to ConfirmationService, which runs the
     * confirmation checklist, one transaction, the per-institute number lock and the idempotency checks, so a
     * double click or retry returns the existing student instead of creating another.
     */
    public function saveStudent(Request $request)
    {
        $type = $request->input("type");
        $sub_institute_id = (int) $request->session()->get("sub_institute_id");
        $id = (int) $request->input("id");

        try {
            $result = app(ConfirmationService::class)->confirm(
                $sub_institute_id,
                $id,
                (int) $request->session()->get("syear"),
                $request->session()->get("term_id"),
                $request->input('override_reason'),
                AdmissionAccess::can('override')
            );
        } catch (AdmissionException $e) {
            $res['status_code'] = 0;
            $res['message'] = $e->getMessage().($e->details ? ' ('.implode('; ', $e->details).')' : '');

            return is_mobile($type, "admission_registration.index", $res);
        }

        $res['status_code'] = 1;
        $res['message'] = $result['created']
            ? "Student added successfully"
            : "Student already exists for this admission (ID ".$result['student_id'].") - no duplicate created";

        return is_mobile($type, "admission_registration.index", $res);
    }

    /** Next enrollment number for a standard (preview only; the number is re-checked under lock at confirmation). */
    public function max_enrollment_no($sub_institute_id, $admission_standard_id)
    {
        return app(NumberService::class)->nextEnrollmentNo((int) $sub_institute_id, $admission_standard_id ? (int) $admission_standard_id : null);
    }

    /** @deprecated kept for callers; previously returned SQL text instead of a number. */
    public function max_enrollment_no_new($sub_institute_id, $admission_standard_id)
    {
        return $this->max_enrollment_no($sub_institute_id, $admission_standard_id);
    }

    public function ajax_getDivision(Request $request)
    {
        $standard_id = $request->input("standard_id");
        $sub_institute_id = session()->get("sub_institute_id");
        $marking_period_id = session()->get('term_id');
        return DB::table('std_div_map as sdm')
            ->join('standard ad s', function ($join) use($marking_period_id) {
                $join->whereRaw('s.id =sdm.standard_id AND s.sub_institute_id = sdm.sub_institute_id');
                // ->when($marking_period_id,function($query) use ($marking_period_id){
                //     $query->where('s.marking_period_id',$marking_period_id);
                // });
            })->join('division ad d', function ($join) {
                $join->whereRaw('d.id = sdm.division_id AND d.sub_institute_id = sdm.sub_institute_id');
            })->selectRaw("d.id,d.name,sdm.standard_id")
            ->where('sdm.sub_institute_id', $sub_institute_id)
            ->where('sdm.standard_id', $standard_id)->get()->toArray();
    }
}
