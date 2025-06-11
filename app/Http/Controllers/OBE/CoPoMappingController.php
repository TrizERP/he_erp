<?php

namespace App\Http\Controllers\OBE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Support\Facades\Validator;
use function App\Helpers\is_mobile;

class CoPoMappingController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $user_id = session()->get('user_id');

        if($type == 'API'){
            try {
                if (!$this->jwtToken()->validate()) {
                    $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];
    
                    return response()->json($response, 200);
                }
            } catch (\Exception $e) {
                $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];
    
                return response()->json($response, 200);
            }

            $sub_institute_id = $request->get('sub_institute_id');
            $syear = $request->get('syear');
            $user_id = $request->get('user_id');
            
            $validator = Validator::make($request->all(), [
                'sub_institute_id' => 'required|numeric',
                'syear' => 'required|numeric',
                'user_id' => 'required|numeric',
            ]);

            if($validator->fails()){
                $response = ['status' => '2', 'message' => $validator->errors(), 'data' => []];
                return response()->json($response, 200);
            }
        }

        return is_mobile($type, "OBE/CoPoMapping", null, "view");
    }

    public function create(Request $request)
    {
        $type = $request->input('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $user_id = session()->get('user_id');

        if($type == 'API'){
            try {
                if (!$this->jwtToken()->validate()) {
                    $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];
    
                    return response()->json($response, 200);
                }
            } catch (\Exception $e) {
                $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];
    
                return response()->json($response, 200);
            }

            $sub_institute_id = $request->get('sub_institute_id');
            $syear = $request->get('syear');
            $user_id = $request->get('user_id');
            
            $validator = Validator::make($request->all(), [
                'sub_institute_id' => 'required|numeric',
                'syear' => 'required|numeric',
                'user_id' => 'required|numeric',
                'grade' => 'required|numeric',
                'standard' => 'required|numeric',
                'subject' => 'required|numeric',
            ]);

            if($validator->fails()){
                $response = ['status' => '2', 'message' => $validator->errors(), 'data' => []];
                return response()->json($response, 200);
            }
        }

        $grade_id = $request->input('grade');
        $standard_id = $request->input('standard'); 
        $subject_id = $request->input('subject');

        $getCoData = DB::table('lo_category')
                ->where('sub_institute_id', $sub_institute_id)
                ->where('syear', $syear)
                ->where('grade_id', $grade_id)
                ->where('standard_id', $standard_id)
                ->where('subject_id', $subject_id)
                ->where('show_hide', 1)
                ->orderBy('sort_order')
                ->get()->toArray();

        $getPoData = DB::table('lo_master')
                ->where('sub_institute_id', $sub_institute_id)
                ->where('syear', $syear)
                ->where('grade_id', $grade_id)
                // ->where('standard_id', $standard_id)
                // ->where('subject_id', $subject_id)
                ->where('show_hide', 1)
                ->orderBy('sort_order')
                ->get()->toArray();

        $addedData = DB::table('tblco_po_mapping')
                ->where('sub_institute_id', $sub_institute_id)
                ->where('syear', $syear)
                ->where('grade_id', $grade_id)
                ->where('standard_id', $standard_id)
                ->where('subject_id', $subject_id)
                ->pluck('po_json','co_id')->toArray();

        if(!empty($getCoData) && !empty($getPoData)){
            $res['status'] = 1;
            $res['message'] = "Data Found";
        }
        else if(empty($getCoData)){
            $res['status'] = 0;
            $res['message'] = "No CO Data Found";
        }
        else if(empty($getPoData)){
            $res['status'] = 0;
            $res['message'] = "No PO Data Found";
        }
        // echo "<pre>";
        // print_r($addedData);die;
        $res['grade_id'] = $grade_id;
        $res['standard_id'] = $standard_id;
        $res['subject_id'] = $subject_id;
        $res['co_data'] = $getCoData;
        $res['po_data'] = $getPoData;
        $res['addedData'] = $addedData;
        return is_mobile($type, "OBE/CoPoMapping", $res, "view");
    }

    public function store(Request $request){
        $type = $request->input('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $user_id = session()->get('user_id');

        if($type == 'API'){
            try {
                if (!$this->jwtToken()->validate()) {
                    $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];
    
                    return response()->json($response, 200);
                }
            } catch (\Exception $e) {
                $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];
    
                return response()->json($response, 200);
            }

            $sub_institute_id = $request->get('sub_institute_id');
            $syear = $request->get('syear');
            $user_id = $request->get('user_id');
            
            $validator = Validator::make($request->all(), [
                'sub_institute_id' => 'required|numeric',
                'syear' => 'required|numeric',
                'user_id' => 'required|numeric',
                'grade' => 'required|numeric',
                'standard' => 'required|numeric',
                'subject' => 'required|numeric',
                'poInput'=> 'required|array',
            ]);

            if($validator->fails()){
                $response = ['status' => '2', 'message' => $validator->errors(), 'data' => []];
                return response()->json($response, 200);
            }
        }

        $poInput = $request->poInput;
        $grade_id = $request->input('grade_id');
        $standard_id = $request->input('standard_id');
        $subject_id = $request->input('subject_id');

        $poJson = null;
        $i=0;
        foreach ($poInput as $coId => $poData) {
            $poJson = json_encode($poData);
            $insertData = [
                'grade_id' => $grade_id,
                'standard_id' => $standard_id,
                'subject_id' => $subject_id,
                'co_id' => $coId,
                'po_json' => $poJson,
                'sub_institute_id' => $sub_institute_id,
                'syear' => $syear,
            ];

            $checkExists =  DB::table('tblco_po_mapping')->where([
                'grade_id' => $grade_id,
                'standard_id' => $standard_id,
                'subject_id' => $subject_id,
                'co_id' => $coId,
                'sub_institute_id' => $sub_institute_id,
                'syear' => $syear,
            ])->first();

            if(isset($checkExists->id)){
                $insertData['updated_by'] = $user_id;
                $insertData['updated_at'] = now();
                $insert = DB::table('tblco_po_mapping')->where('id', $checkExists->id)->update($insertData);
                if($insert){
                    $i++;
                }
            }else{
                $insertData['created_by'] = $user_id;
                $insertData['created_at'] = now();
                $insert = DB::table('tblco_po_mapping')->insert($insertData);
            }
            if($insert){
                $i++;
            }
        }
        if($i>0){
            $res['status'] = 1;
            $res['message'] = "Data Inserted Successfully";
        }else{
            $res['status'] = 0;
            $res['message'] = "Data Not Inserted";
        }
        // echo "<pre>";   
        // print_r($poJson);die;
        return is_mobile($type, "co_po_mapping.index", $res);
    }
}
