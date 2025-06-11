<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\user\tbluserModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function App\Helpers\is_mobile;

class userReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return Response
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        $tblcustom_fields = $this->customFields($request);

        $tblProfiles = DB::table("tbluserprofilemaster")
            ->where(["sub_institute_id" => session()->get('sub_institute_id')])
            ->orderBy('sort_order', 'asc')
            ->pluck("name", "id");

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $tblcustom_fields;
        $res['profiles'] = $tblProfiles;

        return is_mobile($type, "user/show_user_report", $res, "view");
    }

    public function customFields(Request $request)
    {

        $tblcustom_fields['first_name'] = 'First Name';
        $tblcustom_fields['middle_name'] = 'Middle Name';
        $tblcustom_fields['last_name'] = 'Last Name';
        $tblcustom_fields['mobile'] = 'Mobile';
        // $tblcustom_fields['father_name'] = 'Father Name';
        $tblcustom_fields['gender'] = 'Gender';
        $tblcustom_fields['birthdate'] = 'Birthdate';
        $tblcustom_fields['email'] = 'Email';
        // $tblcustom_fields['username'] = 'Username';
        $tblcustom_fields['city'] = 'City';
        $tblcustom_fields['state'] = 'State';
        $tblcustom_fields['address'] = 'Address';
        $tblcustom_fields['pincode'] = 'Pincode';
        $tblcustom_fields['designation'] = 'Designation';

        $tblcustoms = DB::table("tblcustom_fields")
            ->where(["sub_institute_id" => session()->get('sub_institute_id'), "table_name" => "tbluser"])
            ->pluck("field_label", "field_name");

        $customfieldArray = [];
        foreach ($tblcustoms as $key => $value) {
            $customfieldArray[$key] = $value;
        }

        return array_merge($tblcustom_fields, $customfieldArray);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return void
     */
    public function store(Request $request)
    {
        //
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
     * @param  int  $id
     * @return void
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return void
     */
    public function destroy($id)
    {
        //
    }

    public function searchUser(Request $request)
    {
        $profile = $request->input("profile");
        $type = $request->input('type');
        $status = $request->input("status");
        $department_id = $request->input("department_id");
        $emp_id = $request->input("emp_id");
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $syear = $request->session()->get('syear');

        $tblProfiles = DB::table("tbluserprofilemaster")
            ->where(["sub_institute_id" => session()->get('sub_institute_id')])
            ->orderBy('sort_order', 'asc')
            ->pluck("name", "id");

        $header = [];
        $searchArr = ['_'];
        $replaceArr = [' '];
        if ($request->input('dynamicFields') == '') {
            $res['status_code'] = 0;
            $res['message'] = "Please select one checkbox atlease to view report";

            return is_mobile($type, "user_report.index", $res);
        }
        foreach ($request->input('dynamicFields') as $key => $value) {
            $value1 = str_replace($searchArr, $replaceArr, $value);
            $header[$value] = ucfirst($value1);
        }
        $extraSearchArray = [];
        $extraSearchArray['tbluser.sub_institute_id'] = $sub_institute_id;
        $extraSearchArray['tbluser.status'] = $status;
        if(isset($profile) && $profile!=''){
            $extraSearchArray['tbluser.user_profile_id'] = $profile;
        }
        if(isset($department_id) && $department_id!=0){
            $extraSearchArray['tbluser.department_id'] = $department_id;
        }
        if(isset($emp_id) && $emp_id!=0){
            $extraSearchArray['tbluser.id'] = $emp_id;
        }
        $user_data = tbluserModel::selectRaw('tbluser.*,tbluserprofilemaster.name as designation')
            ->join('tbluserprofilemaster', 'tbluser.user_profile_id', '=', 'tbluserprofilemaster.id')
            ->leftJoin('hrms_departments',function($q) use($sub_institute_id){
                $q->on('tbluser.department_id',"=","hrms_departments.id")->where('hrms_departments.sub_institute_id',$sub_institute_id);
            })
            ->where($extraSearchArray)
            ->get();

        $res['status_code'] = 1;
        $res['message'] = "Student List";
        $res['user_data'] = $user_data;
        $res['data'] = $this->customFields($request);
        $res['headers'] = $header;
        $res['profiles'] = $tblProfiles;
        $res['profile'] = $profile;
        $res['status'] = $status;
        $res['department_id'] = $request->department_id;
        $res['selected_emp'] = $request->emp_id;
        
        return is_mobile($type, "user/show_user_report", $res, "view");

    }
}
