<?php

namespace App\Http\Controllers\OBE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Support\Facades\Validator;
use function App\Helpers\is_mobile;
use App\Models\OBE\addCourseCO;

class addCourseCOController extends Controller
{
    use GetsJwtToken;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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

        $res['addedData'] = addCourseCO::join('sub_std_map as ssm','tbladd_course_co.course','=','ssm.subject_id')
            ->selectRaw('tbladd_course_co.*,ssm.display_name as course_name')
            ->where('tbladd_course_co.sub_institute_id', $sub_institute_id)
            ->where('tbladd_course_co.syear', $syear)
            ->whereNull('tbladd_course_co.deleted_at')
            ->get();

        $res['semesterLists'] = DB::table('standard')->where('sub_institute_id', $sub_institute_id)
            ->orderBy('sort_order')
            ->pluck('name','id');
        // echo "<pre>";
        // print_r($res['semesterLists']);die;
        return is_mobile($type, "OBE/add_course_co", $res, "view");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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
        $data = $request->except(['_token','token','type', 'submit']);
        $data['sub_institute_id'] = $sub_institute_id;
        $data['syear'] = $syear;
        $data['created_by'] = $user_id;
        $data['created_at'] = now();

        // $addCourseCO = new addCourseCO();
        // $addCourseCO->fill($data);
        // $addCourseCO->save();
        $addCourseCO = addCourseCO::insert($data);  
        if($addCourseCO){
            $response = ['status' => '1', 'message' => 'Data saved successfully'];
        }
        else{
            $response = ['status' => '0', 'message' => 'Failed to save data'];
        }
        
        // echo "<pre>";
        // print_r($request->all());die;
        return is_mobile($type, "add_course_co.index", $response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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
        // echo "<pre>";
        // print_r($request->all());die;   
        $data = $request->except(['_token','_method','method','token','type', 'submit']);
        $data['updated_by'] = $user_id;
        $data['updated_at'] = now();

        // $addCourseCO = new addCourseCO();
        // $addCourseCO->fill($data);
        // $addCourseCO->save();
        $addCourseCO = addCourseCO::where('id',$id)->update($data);  
        if($addCourseCO){
            $response = ['status' => '1', 'message' => 'Data updated successfully'];
        }
        else{
            $response = ['status' => '0', 'message' => 'Failed to update data'];
        }
        
        // echo "<pre>";
        // print_r($request->all());die;
        return is_mobile($type, "add_course_co.index", $response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
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
        // echo "<pre>";
        // print_r($request->all());die;   
        $data['deleted_by'] = $user_id;
        $data['deleted_at'] = now();

        // $addCourseCO = new addCourseCO();
        // $addCourseCO->fill($data);
        // $addCourseCO->save();
        $addCourseCO = addCourseCO::where('id',$id)->update($data);  
        if($addCourseCO){
            $response = ['status' => '1', 'message' => 'Data deleted successfully'];
        }
        else{
            $response = ['status' => '0', 'message' => 'Failed to delete data'];
        }
        
        // echo "<pre>";
        // print_r($request->all());die;
        return is_mobile($type, "add_course_co.index", $response);
    }
}
