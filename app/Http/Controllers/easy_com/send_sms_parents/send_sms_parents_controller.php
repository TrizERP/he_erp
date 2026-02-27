<?php

namespace App\Http\Controllers\easy_com\send_sms_parents;

use App\Http\Controllers\Controller;
use App\Models\easy_com\manage_sms_api\manage_sms_api;
use App\Models\sms\SmsRemarkMaster;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use function App\Helpers\is_mobile;
use function App\Helpers\SearchStudent;


class send_sms_parents_controller extends Controller
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
        }

        $data['data'] = array();
        $type = $request->input('type');

        $number_types = [
            "mobile"   => "Father Mobile",
            "student_mobile" => "Student Mobile",
            "mother_mobile" => "Mother Mobile",
        ];
        
        $data['number_types'] =$number_types;

        return is_mobile($type, "easy_comm/send_sms_parents/show", $data, "view");
    }

    //13.46

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $type = $request->input('type');
        
        // Check if required parameters exist
        if (!$request->has('grade') || !$request->has('standard') || !$request->has('division')) {
            return redirect()->route('send_sms_parents.index')->with('error', 'Missing required parameters');
        }
        
        $student_data = SearchStudent($request->input('grade'), $request->input('standard'), $request->input('division'));
        $responce_arr['grade'] = $request->input('grade');
        $responce_arr['standard'] = $request->input('standard');
        $responce_arr['division'] = $request->input('division');
        $responce_arr['number_type'] = $request->input('number_type');

        foreach ($student_data as $id => $arr) {
            $responce_arr['stu_data'][$id]['sr.no'] = $id + 1;
            $responce_arr['stu_data'][$id]['name'] = $arr['first_name'].' '.$arr['middle_name'].' '.$arr['last_name'];
            $responce_arr['stu_data'][$id]['student_id'] = $arr['student_id'];
            // $responce_arr['stu_data'][$id][$_REQUEST['number_type']] = $arr[$_REQUEST['number_type']];
            $responce_arr['stu_data'][$id]['mobile'] = $arr['mobile'];
            $responce_arr['stu_data'][$id]['student_mobile'] = $arr['student_mobile'];
            $responce_arr['stu_data'][$id]['mother_mobile'] = $arr['mother_mobile'];

            $responce_arr['stu_data'][$id]['standard_name'] = $arr['standard_name'];
            $responce_arr['stu_data'][$id]['division_name'] = $arr['division_name'];
            $responce_arr['stu_data'][$id]['enrollment_no'] = $arr['enrollment_no'];
        }
        $number_types = [
            "mobile"   => "Father Mobile",
            "student_mobile" => "Student Mobile",
            "mother_mobile" => "Mother Mobile",
        ];
        
        $responce_arr['number_types'] =$number_types;
        
        // Get SMS remarks from sms_remark_master table
        $responce_arr['sms_remarks'] = SmsRemarkMaster::where('remark_status', 'Y')
            ->orderBy('sort_order')
            ->get();
        
        return is_mobile($type, "easy_comm/send_sms_parents/add", $responce_arr, "view");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        // Validate required inputs
        $request->validate([
            'grade' => 'required',
            'standard' => 'required',
            'division' => 'required',
            'smsText' => 'required'
        ]);
        
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $text = $request->input('smsText');
        $responce = [];
        $student_data = SearchStudent($request->input('grade'), $request->input('standard'), $request->input('division'));

        foreach ($request->input('sendsms') as $number => $on) {
            $responce = $this->sendSMS($number, $text, $sub_institute_id);
            if ($responce['error'] == 1) {
                break;
            } else {
                $student_id = 0;
                foreach ($student_data as $id => $arr) {
                    if ($arr['mobile'] == $number || $arr['student_mobile'] == $number || $arr['mother_mobile'] == $number) {
                        $student_id = $arr['student_id'];
                    }
                }
                $message_id = $responce['message'];
                $this->saveParentLog($student_id, $text, $number, $sub_institute_id, $syear,$message_id);
            }
        }

        if ($responce['error'] == 1) {
            $res = [
                "status_code" => 1,
                "message"     => $responce['message'],
            ];
        } else {
            $res = [
                "status_code" => 1,
                "message"     => "SMS Sent",
            ];
        }

        $type = $request->input('type');

        return is_mobile($type, "send_sms_parents.index", $res, "redirect");
    }

    public function sendSMS($mobile, $text, $sub_institute_id,$template_id='')
    {
        $data = manage_sms_api::where(['sub_institute_id' => $sub_institute_id])
            ->get()->first();

        if ($data) {
            $data = $data->toArray();
            $isError = 0;
            $errorMessage = true;

            $text = urlencode($text);
            $data['last_var'] = urlencode($data['last_var']);
            if($template_id !=''){
                $data['last_var'] = $template_id;
            }

            try {
                $url = $data['url'].$data['pram'].$data['mobile_var'].$mobile.$data['text_var'].$text;
            
                // Send GET request
                $response = Http::withoutVerifying()->get($url);
            
                // Get raw response
                $output = trim($response->body());
            
                // If API returns JSON (most common case)
                $result = json_decode($output);
            
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception("Invalid JSON response: " . json_last_error_msg());
                }
            
                $message_id = $result->data[0]->id ?? null;
            
            } catch (\Exception $e) {
                $isError = true;
                $errorMessage = $e->getMessage();
            }
        } else {
            $isError = 1;
            $errorMessage = "Please add api details first.";
        }
        $responce = [];
        if ($isError) {
            $responce = ['error' => 1, 'message' => $errorMessage];
        } else {
            $responce = ['error' => 0, 'message' => $message_id];
        }

        return $responce;
    }

    public function saveParentLog($student_id, $msg, $number, $sub_institute_id, $syear,$message_id)
    {
        DB::table('sms_sent_parents')->insert([
            'syear'            => $syear,
            'student_id'       => $student_id,
            'sms_text'         => $msg,
            'sms_no'           => $number,
            'module_name'      => 'Parent',
            'message_id'       => $message_id,
            'sub_institute_id' => $sub_institute_id,
        ]);
    }


    public function teacherSendSmsParentsAPI(Request $request)
    {
        try {
            if (! $this->jwtToken()->validate()) {
                $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];

                return response()->json($response, 401);
            }
        } catch (\Exception $e) {
            $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];

            return response()->json($response, 401);
        }

        $type = $request->input("type");
        $teacher_id = $request->input("teacher_id");
        $sub_institute_id = $request->input("sub_institute_id");
        $mobile_number = $request->input("mobile_number");
        $sms_text = $request->input("sms_text");
        $syear = $request->input("syear");

        if ($teacher_id != "" && $sub_institute_id != "" && $sms_text != "" && count($mobile_number) > 0) {
            foreach ($mobile_number as $student_id => $number) {
                $response1 = $this->sendSMS($number, $sms_text, $sub_institute_id);
                if ($response1['error'] == 1) {
                    break;
                } else {
                    $message_id = $response1['message'];
                    $this->saveParentLog($student_id, $sms_text, $number, $sub_institute_id, $syear,$message_id);
                }
            }

            $res['status_code'] = 1;
            $res['message'] = "Successfully sent SMS";
        } else {
            $res['status_code'] = 0;
            $res['message'] = "Parameter Missing";
        }

        return json_encode($res);
    }

    public function send_SMS_Common(Request $request){
        // return $request;
        $sub_institute_id = session()->get('sub_institute_id');
        $chkstudent   = $request->input('student');
        $student_mobile    = $request->input('student_mobile');
        $parent_mobile    = $request->input('parent_mobile');
        $SMS_NAME     = $request->input('SMS_NAME'); // student name
        $SMS_PNAME    = $request->input('SMS_PNAME'); // parent name
        $smsnumber    = $request->input('smsnumber'); // student or parent
        $sendSmsNumber   = ($smsnumber && $smsnumber==="parent_mobile_no") ? $parent_mobile : $student_mobile;
        $messageInput = $request->input('DESC', '');

            foreach ($chkstudent as $stuRow =>$studentId) {
                $SMS_TEXT = $messageInput;

                if ($smsnumber !== 'parent_mobile_no') {
                    $SMS_TEXT = str_replace('[STUDENT_NAME]', $SMS_NAME[$stuRow] ?? '', $SMS_TEXT);
                } else {
                    $SMS_TEXT = str_replace('[PARENT_NAME]', $SMS_PNAME[$stuRow] ?? '', $SMS_TEXT);
                }

                $this->sendSMS($sendSmsNumber, $messageInput, $sub_institute_id);
                // send sms to parent or student
            }

        return redirect()->back()->with('message', 'SMS Sent Successfully');
    }
}   
