<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\HrmsJobTitle;
use App\Models\school_setup\subjectModel;
use App\Models\settings\tblcustomfieldsModel;
use App\Models\settings\tblfields_dataModel;
use App\Models\user\tbluserModel;
use App\Models\user\tbluserprofilemasterModel;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use function App\Helpers\is_mobile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use  App\Models\school_setup\standardModel;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class tbluserController extends Controller
{

    use GetsJwtToken;

    public function index(Request $request)
    {
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $user_profile = $request->session()->get('user_profile_name');

        $user_data = tbluserModel::select('tbluser.*', 'tbluserprofilemaster.name as profile_name',
            DB::raw('if(tbluser.status = 1,"Active","Inactive") as status'))
            ->join('tbluserprofilemaster', 'tbluser.user_profile_id', '=', 'tbluserprofilemaster.id')
            ->where(['tbluser.sub_institute_id' => $sub_institute_id]) //, 'tbluser.status' => "1"
            ->when(!in_array($user_profile,["Admin","Super Admin"]),function($q){
                $q->where('tbluser.id',session()->get('user_id'));
            })
            ->get();

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $user_data;

        $type = $request->input('type');

        return is_mobile($type, "user/show_user", $res, "view");
    }

    public function create(Request $request)
    {
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $data = tbluserprofilemasterModel::where(['sub_institute_id' => $sub_institute_id, 'status' => '1'])->get()->toArray();
        $dataCustomFields = tblcustomfieldsModel::where([
            'sub_institute_id' => $sub_institute_id,
            'status' => "1",
            'table_name' => "tbluser",
        ])
            ->get();

        $subject_data = subjectModel::where(['sub_institute_id' => $sub_institute_id])->get();
        $employees = tbluserModel::where('sub_institute_id', $sub_institute_id)->get();
        $job_titles = HrmsJobTitle::where('sub_institute_id', $sub_institute_id)->get();

        $fieldsData = tblfields_dataModel::get()->toArray();
        $i = 0;
        $finalfieldsData = [];
        foreach ($fieldsData as $key => $value) {
            $finalfieldsData[$value['field_id']][$i]['display_text'] = $value['display_text'];
            $finalfieldsData[$value['field_id']][$i]['display_value'] = $value['display_value'];
            $i++;
        }

        // auto increament 20-04-24
        $maxEmpCode = DB::table('tbluser')->selectRaw("MAX(CAST(employee_no AS INT)) AS new_emp_code")
            ->where('sub_institute_id', $sub_institute_id)->whereRaw('employee_no is not null')->limit(1)->orderBy('id')->get()->toArray();

        $maxEmpCode = array_map(function ($value) {
            return (array) $value;
        }, $maxEmpCode);

        $new_emp_code = ($maxEmpCode['0']['new_emp_code'] + 1) ?? 1;

        $departments = DB::table('hrms_departments')->where('sub_institute_id', $sub_institute_id)->where('status', 1)->get()->toArray();

        if (count($finalfieldsData) > 0) {
            view()->share('data_fields', $finalfieldsData);
        }

        view()->share('new_emp_code', $new_emp_code);
        view()->share('custom_fields', $dataCustomFields);
        view()->share('subject_data', $subject_data);
        view()->share('user_profiles', $data);
        view()->share('job_titles', $job_titles);
        view()->share('employees', $employees);
        view()->share('departments', $departments);

        return view('user/add_user');
    }

    public function store(Request $request)
    {
        //return $request->all();
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $type = $request->input('type');

        $file_name = "";
        if ($request->hasFile('user_image')) {
            $file = $request->file('user_image');
            $originalname = $file->getClientOriginalName();
            $name = $request->get('user_name') . date('YmdHis');
            $ext = File::extension($originalname);
            $file_name = $name . '.' . $ext;
            $path = $file->storeAs('public/user/', $file_name);
        }

        $request->request->add(['image' => $file_name]); //add request
        $data = $this->saveData($request);

        $data = tbluserModel::where(['sub_institute_id' => $sub_institute_id])->get();

        $res['status_code'] = "1";
        $res['message'] = "User created successfully";
        $res['data'] = $data;

        return is_mobile($type, "add_user.index", $res);
    }

    public function saveData(Request $request)
    {
        $newRequest = $request->all();
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $finalArray['sub_institute_id'] = $sub_institute_id;
        $finalArray['status'] = 1;
        unset($newRequest['user_image']);
        foreach ($newRequest as $key => $value) {
            if ($key != '_method' && $key != '_token' && $key != 'submit') {
                if (is_array($value)) {
                    $value = implode(",", $value);
                }
                $finalArray[$key] = $value;
            }
        }
        tbluserModel::insert($finalArray);
        $id = DB::getPdo()->lastInsertId();

        $client_data = DB::table("school_setup as s")
            ->join('tblclient as c', function ($join) {
                $join->whereRaw("c.id = s.client_id");
            })
            ->selectRaw('*,if(db_hrms is null,0,1) as rights')
            ->where("s.Id", "=", $sub_institute_id)
            ->get()->toArray();

        $hrms_db_host = $client_data[0]->db_host;
        $hrms_db_user = $client_data[0]->db_user;
        $hrms_db_password = $client_data[0]->db_password;
        $hrms_db_hrms = $client_data[0]->db_hrms;
        $hrms_rights = $client_data[0]->rights;

        if ($hrms_rights == 1 && $id != "") {
            $fields = [
                'db_host' => $hrms_db_host,
                'db_user' => $hrms_db_user,
                'db_password' => $hrms_db_password,
                'db_hrms' => $hrms_db_hrms,
            ];
            $fields = array_merge($fields, $finalArray);

            //url-ify the data for the POST
            $fields_string = "";
            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            rtrim($fields_string, '&');
            //open connection
            $ch = curl_init();

            $url = "http://" . $_SERVER['HTTP_HOST'] . "/add_user_hrms.php";

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

            //execute post
            $result = curl_exec($ch);

            //close connection
            curl_close($ch);
        }

        return $id;
    }

    public function updateData(Request $request)
    {
        $newRequest = $request->all();
        $user_id = $newRequest['id'];
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $finalArray['sub_institute_id'] = $sub_institute_id;
        $finalArray['status'] = 1;
        unset($newRequest['user_image']);
        foreach ($newRequest as $key => $value) {
            if ($key != '_method' && $key != '_token' && $key != 'submit' && $key != 'id') {
                if (is_array($value)) {
                    $value = implode(",", $value);
                }
                $finalArray[$key] = $value;
            }
        }

        return tbluserModel::where(['id' => $user_id])->update($finalArray);
    }

    public function edit(Request $request, $id)
    {
        $type = $request->input('type');
        $subject_data_selected_arr = array();

        if ($type == "API") {
            $sub_institute_id = $request->input('sub_institute_id');
            $syear = $request->input('syear');
        } else {
            $sub_institute_id = $request->session()->get('sub_institute_id');
            $syear = session()->get('syear');
        }

        $editData = tbluserModel::find($id)->toArray();
        $data = tbluserprofilemasterModel::where(['sub_institute_id' => $sub_institute_id])->get()->toArray();
        $subject_data = subjectModel::where(['sub_institute_id' => $sub_institute_id])->get()->toArray();
        $subject_data_selected = $editData['subject_ids'];

        $past_educations = DB::table('tbluser_past_educations')->where([['user_id', $id], ['sub_institute_id', $sub_institute_id]])->get();
        $experience_details = DB::table('tbluser_experience_details')->where([['user_id', $id], ['sub_institute_id', $sub_institute_id]])->orderBy('joining_date')->get();
        $training_details = DB::table('tbluser_training_details')->where([['user_id', $id], ['sub_institute_id', $sub_institute_id]])->get();
        $professional_details = DB::table('tbluser_professional_details')->where([['user_id', $id], ['sub_institute_id', $sub_institute_id]])->get();
        $salary_details = DB::table('tbluser_salary_details')->where([['user_id', $id], ['sub_institute_id', $sub_institute_id]])->get();
        $sub_std_map = DB::table('sub_std_map')->where(['sub_institute_id' => $sub_institute_id])->get();

        if (isset($subject_data_selected)) {
            $subject_data_selected_arr = explode(",", $subject_data_selected);
        }

        $dataCustomFields = tblcustomfieldsModel::where([
            'sub_institute_id' => $sub_institute_id,
            'status' => "1",
            'table_name' => "tbluser",
        ])
            ->get();

        $fieldsData = tblfields_dataModel::get()->toArray();
        $i = 0;
        $finalfieldsData = array();
        foreach ($fieldsData as $key => $value) {
            $finalfieldsData[$value['field_id']][$i]['display_text'] = $value['display_text'];
            $finalfieldsData[$value['field_id']][$i]['display_value'] = $value['display_value'];
            $i++;
        }

        if (count($finalfieldsData) > 0) {
            $res['data_fields'] = $finalfieldsData;
        }

        $empCode = DB::table('tbluser')->where('id', $id)->first();
        $new_emp_code = $empCode->employee_no;

        $departments = DB::table('hrms_departments')->where('sub_institute_id', $sub_institute_id)->where('status', 1)->get()->toArray();
        // 23-10-2024
        $categorties = DB::table('cast')->where('sub_institute_id', $sub_institute_id)->orderBy('sort_order')->get()->toArray();
        $religions = DB::table('religion')->get()->toArray();
        $bloodgroups = DB::table('blood_group')->get()->toArray();
        $maretial_status = ['Yes', 'No'];
        $res['categorties'] = $categorties;
        $res['religions'] = $religions;
        $res['bloodgroups'] = $bloodgroups;
        $res['maretial_status'] = $maretial_status;
        // 23-10-2024 end
        $res['past_educations'] = $past_educations;
        $res['experience_details'] = $experience_details;
        $res['professional_details'] = $professional_details;
        $res['training_details'] = $training_details;
        $res['salary_details'] = $salary_details;
        $res['documentTypeLists'] = DB::table('student_document_type')->where('status',1)->where('user_type','staff')->get()->toArray();
        $res['documentLists'] = DB::table('staff_document')->select('staff_document.*', 'd.document_type')
        ->join('student_document_type as d', 'd.id', 'staff_document.document_type_id')
        ->where(['sub_institute_id' => $sub_institute_id, 'user_id' => $id])
        ->get()
        ->toArray();
        $res['standardLists'] = standardModel::where('sub_institute_id',$sub_institute_id)->orderBy('sort_order')->get()->toArray();
        $res['sub_std_map'] = $sub_std_map;
        $res['employees'] = tbluserModel::where('sub_institute_id', $sub_institute_id)->get();
        $res['job_titles'] = HrmsJobTitle::where('sub_institute_id', $sub_institute_id)->get();
        $res['departments'] = $departments;
        $res['custom_fields'] = $dataCustomFields;
        $res['subject_data'] = $subject_data;
        $res['subject_data_selected_arr'] = $subject_data_selected_arr;
        $res['user_profiles'] = $data;
        $res['new_emp_code'] = $new_emp_code;
        $res['data'] = $editData;

        return is_mobile($type, "user/edit_user", $res, "view");
    }

    public function update(Request $request, $id)
    {
        if (!$request->monday) {
            $request->request->add(['monday' => 0]);
        }
        if (!$request->tuesday) {
            $request->request->add(['tuesday' => 0]);
        }
        if (!$request->wednesday) {
            $request->request->add(['wednesday' => 0]);
        }
        if (!$request->thursday) {
            $request->request->add(['thursday' => 0]);
        }
        if (!$request->friday) {
            $request->request->add(['friday' => 0]);
        }
        if (!$request->saturday) {
            $request->request->add(['saturday' => 0]);
        }
        if (!$request->sunday) {
            $request->request->add(['sunday' => 0]);
        }
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $type = $request->input('type');

        $file_name = "";
        if ($request->hasFile('user_image')) {
            $file = $request->file('user_image');
            $originalname = $file->getClientOriginalName();
            $name = $request->get('user_name') . date('YmdHis');
            $ext = File::extension($originalname);
            $file_name = $name . '.' . $ext;
            $path = $file->storeAs('public/user/', $file_name);
        }
        if ($file_name != "") {
            $request->request->add(['image' => $file_name]); //add request
            $request->session()->put('image', $file_name);
        }

        $request->request->add(['id' => $id]); //add request
        $user_id = $id;

        $data = $this->updateData($request);

        $res['status_code'] = "1";
        $res['message'] = "User updated successfully";
        $res['data'] = $data;

        return is_mobile($type, "add_user.index", $res);
    }

    public function destroy(Request $request, $id)
    {
        $user = [
            'status' => "0",
        ];
        $type = $request->input('type');
        tbluserModel::where(["id" => $id])->update($user);

        $res['status_code'] = "1";
        $res['message'] = "User deleted successfully";

        return is_mobile($type, "add_user.index", $res);
    }

    public function deactiveUser(Request $request, $id)
    {
        $user = [
            'status' => "0",
        ];
        $type = $request->input('type');
        tbluserModel::where(["id" => $id])->update($user);
        $res['status_code'] = "1";
        $res['message'] = "User deleted successfully";

        return is_mobile($type, "add_user.index", $res);
    }


    public function teacherListAPI(Request $request)
    {

        // try {
        //           if (!$this->jwtToken()->validate()) {
        //               $response = array('status' => '2', 'message' => 'Token Auth Failed', 'data' => array());
        //               return response()->json($response, 401);
        //           }
        //       } catch (\Exception $e) {
        //           $response = array('status' => '2', 'message' => $e->getMessage(), 'data' => array());
        //           return response()->json($response, 401);
        //       }

        $type = $request->input("type");
        $sub_institute_id = $request->input("sub_institute_id");


        if ($sub_institute_id != "") {
            $data = DB::table("tbluser as u")
                ->join('tbluserprofilemaster as up', function ($join) {
                    $join->whereRaw("up.id = u.user_profile_id AND up.name = 'Teacher'");
                })
                ->selectRaw("u.id,concat_ws(' ',u.first_name,u.middle_name,u.last_name) as teacher_name,
					    u.email,u.mobile,u.user_profile_id,up.name as user_group")
                ->where("u.sub_institute_id", "=", $sub_institute_id)
                ->orderBy('u.id')
                ->get()->toArray();

            $res['status_code'] = 1;
            $res['message'] = "Success";
            $res['data'] = $data;
        } else {
            $res['status_code'] = 0;
            $res['message'] = "Parameter Missing";
        }

        return json_encode($res);
    }

    public function storePastEducation(Request $request)
    {
        // echo "<pre>";print_r($request->all());exit;
        // return  DB::table('tbluser_professional_details')->get();
        $sub_institute_id = $request->session()->get('sub_institute_id');
        /*$columns = DB::getSchemaBuilder()->getColumnListing('tbluser_past_educations');
        return $columns;*/
        $i = 0;
        if ($request->user_id) {
            if ($request->dataType == 'education_data') {
                foreach ($request->degree as $key => $degree) {
                    $prepareData = [
                        'user_id' => $request->user_id,
                        'degree' => $degree ?? '',
                        'medium' => $request->get('medium')[$key] ?? '',
                        'university_name' => $request->get('university_name')[$key] ?? '',
                        'passing_year' => $request->get('passing_year')[$key] ?? '',
                        'main_subject' => $request->get('main_subject')[$key] ?? '',
                        'secondary_subject' => $request->get('secondary_subject')[$key] ?? '',
                        'percentage' => $request->get('percentage')[$key],
                        'cpi' => $request->get('cpi')[$key] ?? '',
                        'cgpa' => $request->get('cgpa')[$key] ?? '',
                        'remarks' => $request->get('remarks')[$key] ?? '',
                        'sub_institute_id' => $sub_institute_id,
                    ];

                    if (isset($request->get('past_education_id')[$key]) && $request->get('past_education_id')[$key] != 0) {
                        $prepareData['updated_at'] = now();
                        DB::table('tbluser_past_educations')->where('id', $request->get('past_education_id')[$key])->update($prepareData);
                        // DB::table('tbluser_past_educations')->whereNotIn('id', $request->get('past_education_id'))->where([['user_id', $request->user_id], ['sub_institute_id', $sub_institute_id]])->delete();

                    } else if (isset($degree) || isset($request->get('medium')[$key]) || isset($request->get('university_name')[$key]) || isset($request->get('passing_year')[$key]) || (isset($request->get('main_subject')[$key]) && $request->get('main_subject')[$key] != 0) || isset($request->get('secondary_subject')[$key]) || isset($request->get('percentage')[$key]) || isset($request->get('cpi')[$key]) || isset($request->get('remarks')[$key])) {
                        // if ($degree == null && $request->get('medium')[$key] == null && $request->get('university_name')[$key] == null && $request->get('passing_year')[$key] == null
                        //     && $request->get('main_subject')[$key] == null && $request->get('secondary_subject')[$key] == null && $request->get('percentage')[$key] == null && $request->get('cpi')[$key] == null && $request->get('cgpa')[$key] == null && $request->get('remarks')[$key] == null
                        // ) {
                        //     DB::table('tbluser_past_educations')->whereNotIn('id', $request->get('past_education_id'))->where([['user_id', $request->user_id], ['sub_institute_id', $sub_institute_id]])->delete();
                        // } else {
                        //     DB::table('tbluser_past_educations')->insert($prepareData);
                        // }
                        $prepareData['created_at'] = now();
                        DB::table('tbluser_past_educations')->insert($prepareData);
                    }
                }

                $res['success'] = "Data Added Succesfully";

                return redirect()->back()->with($res);
                //echo "<pre>";print_r($prepareData);exit;
                // exit;
                // DB::table('tbluser_past_educations')->whereNotIn('id', $request->get('past_education_id'))->where([['user_id', $request->user_id], ['sub_institute_id', $sub_institute_id]])->delete();
            } else if ($request->dataType == 'experience_detail') {
                //echo "<pre>";print_r($request->teching_type);exit;
                foreach ($request->teching_type as $key => $teching_type) {
                    $joiningDateRaw = $request->get('joining_date')[$key] ?? null;
                    $leavingDateRaw = $request->get('leaving_date')[$key] ?? null;

                    $prepareData = [
                        'user_id'           => $request->user_id,
                        'teching_type'      => $teching_type,
                        'institutional_name'=> $request->get('institutional_name')[$key] ?? '',
                        'designation_name'  => $request->get('designation_name')[$key] ?? '',
                        'experience_type'   => $request->get('experience_type')[$key] ?? '',
                        'joining_date'      => $joiningDateRaw 
                                                ? Carbon::createFromFormat('d-m-Y', $joiningDateRaw)->format('Y-m-d') 
                                                : null,
                        'leaving_date'      => $leavingDateRaw 
                                                ? Carbon::createFromFormat('d-m-Y', $leavingDateRaw)->format('Y-m-d') 
                                                : null,
                        'experience'        => $request->get('experience')[$key] ?? 0,
                        'remarks'           => $request->get('remarks')[$key] ?? '',
                        'sub_institute_id'  => $sub_institute_id,
                    ];
                    if (isset($request->get('experience_detail_id')[$key]) && $request->get('experience_detail_id')[$key] != 0) {
                        DB::table('tbluser_experience_details')->where('id', $request->get('experience_detail_id')[$key])->update($prepareData);
                    } elseif (isset($teching_type) || isset($request->get('institutional_name')[$key]) || isset($request->get('designation_name')[$key]) || isset($request->get('joining_date')[$key]) || isset($request->get('leaving_date')[$key]) || isset($request->get('experience_type')[$key])) {
                        DB::table('tbluser_experience_details')->insert($prepareData);
                    }
                }

                $res['success'] = "Data Added Succesfully";

                return redirect()->back()->with($res);
            } else if ($request->dataType == 'training_details') {
                foreach ($request->training_name as $key => $training_name) {
                    $prepareData = [
                        'user_id' => $request->user_id,
                        'training_name' => $training_name,
                        'training_subject' => $request->get('training_subject')[$key],
                        'training_place' => $request->get('training_place')[$key],
                        'start_date' => $request->get('start_date')[$key],
                        'end_date' => $request->get('end_date')[$key],
                        'days' => isset($request->get('days')[$key]) ? $request->get('days')[$key] : 0,
                        'sub_institute_id' => $sub_institute_id,
                    ];
                    if (isset($request->get('training_detail_id')[$key]) && $request->get('training_detail_id')[$key] != 0) {
                        DB::table('tbluser_training_details')->where('id', $request->get('training_detail_id')[$key])->update($prepareData);
                        DB::table('tbluser_training_details')->whereNotIn('id', $request->get('training_detail_id'))->where([['user_id', $request->user_id], ['sub_institute_id', $sub_institute_id]])->delete();
                    } else if (isset($training_name) || isset($request->get('training_subject')[$key]) || isset($request->get('training_place')[$key]) || isset($request->get('start_date')[$key]) || isset($request->get('end_date')[$key])) {
                        DB::table('tbluser_training_details')->insert($prepareData);
                    }
                }

                $res['success'] = "Data Added Succesfully";

                return redirect()->back()->with($res);
            } else if ($request->dataType == 'professional_details') {
                foreach ($request->designation as $key => $designation) {
                    if (isset($designation) && $designation != null && $designation != '') {
                        $prepareData = [
                            'user_id' => $request->user_id,
                            'designation' => $designation,
                            'appointment_type' => isset($request->get('appointment_type')[$key]) ? $request->get('appointment_type')[$key] : 0,
                            'doctorate_degree' => isset($request->get('doctorate_degree')[$key]) ? $request->get('doctorate_degree')[$key] : 0,
                            'doctorate_degree_percentage' => isset($request->get('doctorate_degree_percentage')[$key]) ? $request->get('doctorate_degree_percentage')[$key] : 0,
                            'pg_degree' => isset($request->get('pg_degree')[$key]) ? $request->get('pg_degree')[$key] : 0,
                            'pg_degree_percentage' => isset($request->get('pg_degree_percentage')[$key]) ? $request->get('pg_degree_percentage')[$key] : 0,
                            'ug_degree' => isset($request->get('ug_degree')[$key]) ? $request->get('ug_degree')[$key] : 0,
                            'ug_degree_percentage' => isset($request->get('ug_degree_percentage')[$key]) ? $request->get('ug_degree_percentage')[$key] : 0,
                            'other_qualification' => isset($request->get('other_qualification')[$key]) ? $request->get('other_qualification')[$key] : 0,
                            'other_qualification_percentage' => isset($request->get('other_qualification_percentage')[$key]) ? $request->get('other_qualification_percentage')[$key] : 0,
                            'specification' => isset($request->get('specification')[$key]) ? $request->get('specification')[$key] : 0,
                            'national_publication' => isset($request->get('national_publication')[$key]) ? $request->get('national_publication')[$key] : 0,
                            'international_publication' => isset($request->get('international_publication')[$key]) ? $request->get('international_publication')[$key] : 0,
                            'no_of_books_published' => isset($request->get('no_of_books_published')[$key]) ? $request->get('no_of_books_published')[$key] : 0,
                            'no_of_patents' => isset($request->get('no_of_patents')[$key]) ? $request->get('no_of_patents')[$key] : 0,
                            'teaching_experience' => isset($request->get('teaching_experience')[$key]) ? $request->get('teaching_experience')[$key] : 0,
                            'total_work_experience' => isset($request->get('total_work_experience')[$key]) ? $request->get('total_work_experience')[$key] : 0,
                            'research_experience' => isset($request->get('research_experience')[$key]) ? $request->get('research_experience')[$key] : 0,
                            'no_of_projects_guided' => isset($request->get('no_of_projects_guided')[$key]) ? $request->get('no_of_projects_guided')[$key] : 0,
                            'no_of_doctorate_students_guided' => isset($request->get('no_of_doctorate_students_guided')[$key]) ? $request->get('no_of_doctorate_students_guided')[$key] : 0,
                            'sub_institute_id' => $sub_institute_id,
                        ];
                        if ($request->get('professional_detail_id')[$key] != 0) {
                            DB::table('tbluser_professional_details')->where('id', $request->get('professional_detail_id')[$key])->update($prepareData);
                            DB::table('tbluser_professional_details')->whereNotIn('id', $request->get('professional_detail_id'))->where([['user_id', $request->user_id], ['sub_institute_id', $sub_institute_id]])->delete();
                        } else {
                            DB::table('tbluser_professional_details')->insert($prepareData);
                        }
                    } else {
                        $i++;
                    }
                }
            } else if ($request->dataType == 'salary_details') {
                foreach ($request->pay_scale as $key => $pay_scale) {
                    if (isset($pay_scale) && $pay_scale != null && $pay_scale != '') {
                        $prepareData = [
                            'user_id' => $request->user_id,
                            'pay_scale' => $pay_scale,
                            'increment_date' => $request->get('increment_date')[$key],
                            'salary_mode' => $request->get('salary_mode')[$key],
                            'basic' => $request->get('basic')[$key],
                            'grade_pay' => $request->get('grade_pay')[$key],
                            'basic_pay' => $request->get('basic_pay')[$key],
                            'da' => $request->get('da')[$key],
                            'da_percentage' => $request->get('da_percentage')[$key],
                            'cla' => $request->get('cla')[$key],
                            'hra' => $request->get('hra')[$key],
                            'hra_percentage' => $request->get('hra_percentage')[$key],
                            'vehicle_allowances' => $request->get('vehicle_allowances')[$key],
                            'medical_allowances' => $request->get('medical_allowances')[$key],
                            'other_allowances' => $request->get('other_allowances')[$key],
                            'gross_salary' => $request->get('gross_salary')[$key],
                            'bank_account_number' => $request->get('bank_account_number')[$key],
                            'bank_name' => $request->get('bank_name')[$key],
                            'bank_ifsc_code' => $request->get('bank_ifsc_code')[$key],
                            'bank_branch' => $request->get('bank_branch')[$key],
                            'pf_number' => $request->get('pf_number')[$key],
                            'sub_institute_id' => $sub_institute_id,
                        ];
                        if ($request->get('salary_detail_id')[$key] != 0) {
                            DB::table('tbluser_salary_details')->where('id', $request->get('salary_detail_id')[$key])->update($prepareData);
                            DB::table('tbluser_salary_details')->whereNotIn('id', $request->get('salary_detail_id'))->where([['user_id', $request->user_id], ['sub_institute_id', $sub_institute_id]])->delete();
                        } else {
                            if (
                                $pay_scale == null && $request->get('increment_date')[$key] == null && $request->get('salary_mode')[$key] == null && $request->get('basic')[$key] == null
                                && $request->get('grade_pay')[$key] == null && $request->get('basic_pay')[$key] == null
                                && $request->get('da')[$key] == null && $request->get('da_percentage')[$key] == null
                                && $request->get('cla')[$key] == null && $request->get('hra')[$key] == null
                                && $request->get('hra_percentage')[$key] == null && $request->get('vehicle_allowances')[$key] == null
                                && $request->get('medical_allowances')[$key] == null && $request->get('other_allowances')[$key] == null
                                && $request->get('gross_salary')[$key] == null && $request->get('bank_account_number')[$key] == null
                                && $request->get('bank_name')[$key] == null && $request->get('bank_ifsc_code')[$key] == null
                                && $request->get('bank_branch')[$key] == null && $request->get('pf_number')[$key] == null
                            ) {
                                DB::table('tbluser_salary_details')->whereNotIn('id', $request->get('salary_detail_id'))->where([['user_id', $request->user_id], ['sub_institute_id', $sub_institute_id]])->delete();
                            } else {
                                DB::table('tbluser_salary_details')->insert($prepareData);
                            }
                        }
                    } else {
                        $i++;
                    }
                }
            } else if ($request->dataType == 'document_details') {
                $j=0;
                foreach ($request->document_title as $key => $document_title) {
                    // Skip if document title is empty
                    if (empty($document_title)) {
                        continue;
                    }

                    $filename = null;
                    $document_detail_id = $request->get('document_detail_id')[$key] ?? 0;

                    // Handle file upload
                    if ($request->hasFile('file.' . $key) || $request->hasFile('new_file.' . $key)) {
                        $file = null;

                        // Check for file in 'file' array
                        if ($request->hasFile('file.' . $key)) {
                            $file = $request->file('file')[$key];
                        }
                        // Check for file in 'new_file' array
                        else if ($request->hasFile('new_file.' . $key)) {
                            $file = $request->file('new_file')[$key];
                        }

                        if ($file && $file->isValid()) {
                            // Generate unique filename
                            $filename = rand(111111111, 9999999999) . '-' . $file->getClientOriginalName();

                            // Store file on DigitalOcean
                            $storagePath = 'public/he_staff_document/' . $filename;

                            // Delete existing file if it exists
                            if (Storage::disk('digitalocean')->exists($storagePath)) {
                                Storage::disk('digitalocean')->delete($storagePath);
                            }

                            // Store the new file
                            Storage::disk('digitalocean')->putFileAs('public/he_staff_document/', $file, $filename, 'public');
                        }
                    }

                    // Prepare data for database
                    $prepareData = [
                        'user_id' => $request->user_id,
                        'document_title' => $document_title,
                        'sub_institute_id' => $sub_institute_id,
                    ];

                    // Only add file to data if we have a filename
                    if ($filename) {
                        $prepareData['file'] = $filename;
                    }

                    // Update existing record or insert new one
                    if ($document_detail_id != 0) {
                        // Update existing record
                        if ($filename) {
                            // Only update if we have a new file
                            $prepareData['updated_at'] = now();
                            $update = DB::table('tbluser_staff_document_details')
                                ->where('id', $document_detail_id)
                                ->update($prepareData);
                            if($update){
                                $j++;
                            }
                        }
                    } else {
                        // Insert new record only if we have a file
                        if ($filename) {
                            $prepareData['created_at'] = now();
                            $insert=DB::table('tbluser_staff_document_details')->insert($prepareData);
                            if($insert){
                                $j++;
                            }
                        }
                    }
                }

                // Clean up records that are no longer needed
                $existingIds = array_filter($request->get('document_detail_id', []));
                if (!empty($existingIds)) {
                    DB::table('tbluser_staff_document_details')
                        ->where('user_id', $request->user_id)
                        ->where('sub_institute_id', $sub_institute_id)
                        ->whereNotIn('id', $existingIds)
                        ->delete();
                }

                if ($j > 0) {
                    $res['success'] = "Data Added Succesfully";
                } else {
                    $res['error'] = "Failed to add that Data!";
                }
                return redirect()->back()->with($res);
                // echo "<pre>";print_r($request->all());exit;
                // DB::table('tbluser_staff_document_details')->whereNotIn('id', $request->get('document_detail_id'))->where([['user_id', $request->user_id], ['sub_institute_id', $sub_institute_id]])->delete();
            }
        }
        // exit;
        // $res['status_code'] = "1";
        if ($i = 0) {
            $res['success'] = "Data Added Succesfully";
        } else {
            $res['error'] = "Found Some Null Inputs. Failed to add that Data!";
        }

        return redirect()->back()->with($res);
    }

    function addUserDocument(Request $request,$id){
        $type = $request->type;
        $document = $request->document;
        $doc_type= $request->document_type_id; 
        $document_title = $request->document_title;
        $sub_institute_id = session()->get('sub_institute_id');
        if($type=="API"){
            $sub_institute_id= $request->sub_institute_id;
        }
        $filename='';
        if($request->hasFile('document')){
            $file = $request->file('document');
            $originalname = $file->getClientOriginalName();
            $name = $id.date('YmdHis');
            $ext = File::extension($originalname);
            $file_name = $name.'.'.$ext;
            Storage::disk('digitalocean')->putFileAs('public/he_staff_document/', $file, $file_name, 'public');
        }

        $data = [
            'user_id'          => $id,
            'document_title'   => $request->get('document_title'),
            'document_type_id' => $request->get('document_type_id'),
            'file_name'        => $file_name,
            'sub_institute_id' => $sub_institute_id,
            'created_at'       => now(),
        ];

        $insert = DB::table('staff_document')->insert($data);

        if($insert){
            $res['success'] = 1;
            $res['message'] = "Document Added successfully";
        }else{
            $res['fail'] = 0;
            $res['message'] = "Failed to Add Document";
        }
        return redirect()->back()->with('success', 'Document updated successfully');
    }

    public function deleteData(Request $request, $id)
    {
        // return $id;
        $delete = '';
        if (isset($request->table_name)) {
            $delete = DB::table($request->table_name)->where('id', $id)->delete();
        }

        if ($delete != '') {
            $res['status'] = 1;
            $res['message'] = "Data Deleted Successfully";
        } else {
            $res['status'] = 0;
            $res['message'] =  "Something went wrong !";
        }
        return response()->json($res);
    }
    public function deleteDocument($id)
    {
        $sub_institute_id = session()->get('sub_institute_id');

        $document = DB::table('staff_document')
        ->where(['sub_institute_id' => $sub_institute_id, 'id' => $id])
        ->first();

        // Delete file from DigitalOcean
        if ($document->file_name) {
            Storage::disk('digitalocean')->delete('public/he_staff_document/'.$document->file_name);
        }

        // Delete record from database
        DB::table('staff_document')
        ->where(['sub_institute_id' => $sub_institute_id, 'id' => $id])
        ->delete();

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}
