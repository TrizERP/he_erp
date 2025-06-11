<?php

namespace App\Http\Controllers\admission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use function App\Helpers\is_mobile;
use DB;

class admissionStatusController extends Controller
{
    //
    public function index(Request $request){
        $type="webForm";
        $res = '';
        return is_mobile($type, 'admission/admission_status/login', $res, 'view');
    }

    public function store(Request $request){
        $type="webForm";
        $sub_institute_id = $request->sub_institute_id;
        $enquiry_no = $request->enquiry_no;
        $email = $request->email;
        $password = $request->password;

        session()->put('sub_institute_id',$sub_institute_id);

        $CheckUserData = DB::table('admission_enquiry')->where(['sub_institute_id'=>$sub_institute_id,'enquiry_no'=>$enquiry_no,'email'=>$email,'admission_password'=>$password])->first();

        if(!empty($CheckUserData)){
            $CheckUserData->admission_standard_name = DB::table('standard')->where('id',$CheckUserData->admission_standard)->value('name');
            $CheckUserData->previous_standard_name = DB::table('standard')->where('id',$CheckUserData->previous_standard)->value('name');
            $CheckUserData->category_name=DB::table('caste')->where('id',$CheckUserData->category)->value('caste_name');

            $Checkregistration = DB::table('admission_form')->where(['sub_institute_id'=>$sub_institute_id,'enquiry_no'=>$enquiry_no,'enquiry_id'=>$CheckUserData->id])->first();

            $Checkconfirmation = DB::table('admission_registration')->where(['sub_institute_id'=>$sub_institute_id,'enquiry_no'=>$enquiry_no,'enquiry_id'=>$CheckUserData->id])->first();

            $res['type'] = $type;
            $res['details'] = $CheckUserData;
            $res['registration'] = $Checkregistration;
            $res['confirmation'] = $Checkconfirmation;

            return is_mobile($type, "admission/admission_status/admission_dashboard", $res,"view");
        }else{
            $res['status_code'] = 0;
            $res['message'] = "Unable to log you in. Please confirm your credentials and try once more.";

            return is_mobile($type, "admission/admission_status/login", $res,"view");
        }

        echo "<pre>";print_r($request->all());exit;
    }

    public function create(Request $request)
    {
        $sub_institute_id = session()->get('sub_institute_id');
        //logout user
        $request->session()->flush();

        // redirect to homepage
        return redirect('/admission_status?sub_institute_id='.$sub_institute_id);
    }
}
