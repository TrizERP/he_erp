<?php

namespace App\Http\Controllers\sqaa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use function App\Helpers\is_mobile;
use App\Models\sqaa\sqaa_master;
use App\Models\sqaa\sqaa_mark;
use App\Models\sqaa\sqaa_document;
use App\Models\sqaa\naac_partA1;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use DB;
use PDF;

class sqaaPartsController extends Controller
{
    //
    public function index(Request $request)
    {
        $type=$request->type;
        $sub_institute_id= session()->get('sub_institute_id');
        $res['partA1'] = naac_partA1::where('sub_institute_id',$sub_institute_id)->first();
        $res['yesOrNo'] = ['No','Yes'];
        $res['InstituteType']=['Co-education','Men','Women'];
        $res['Location']=['Rural','Semi-Urban','Urban'];        
        $res['FinancialStatus'] = ['Grant-in aid','UGC 2f and 12 (B)','Self-financing']; 
        // echo "<pre>";print_r($res['partA1']);exit;
        return is_mobile($type, "sqaa/add_partA1", $res, "view");
    }

    public function store(Request $request)
    {
        $type = $request->type;
        $sub_institute_id=session()->get('sub_institute_id');
        $insert = $this->insertOrUpdate($request,$sub_institute_id,'');
        $res['status_code']=1;
        $res['message']='Inserted Successfully';
        // echo "<pre>";print_r($res['partA1']);exit;
        return is_mobile($type, "naac_parts.index", $res, "redirect");
    }

    public function update(Request $request,$id)
    {
        // echo "<pre>";print_r($statutory_date);exit;
        $type = $request->type;
        $sub_institute_id=session()->get('sub_institute_id');
        $update = $this->insertOrUpdate($request,$sub_institute_id,$id);
        $res['status_code']=1;
        $res['message']='Updated Successfully';
        return is_mobile($type, "naac_parts.index", $res, "redirect");
    }
   
    public function AddInDigiOcean($requestFile){
        $sub_institute_id=session()->get('sub_institute_id');
        $extension = $requestFile->getClientOriginalExtension();   
        $filename = $sub_institute_id.'_'.$requestFile->getClientOriginalName();
        $filePath = 'public/naac/' . $filename;
        Storage::disk('digitalocean')->putFileAs('public/naac/', $requestFile, $filename, 'public');
        return $filename;
    }
    
    public function insertOrUpdate(Request $request,$sub_institute_id,$id){
        
        if ($request->hasFile('assurance_file')) {
            $file = $request->file('assurance_file');   
            $assurance_file= $this->AddInDigiOcean($file);
        }else if($request->has('assurance_file_name')){
            $assurance_file=$request->get('assurance_file_name');
        }

        if ($request->hasFile('conferred_status_file')) {
            $file = $request->file('conferred_status_file');   
            $conferred_status_file= $this->AddInDigiOcean($file);
        }else if($request->has('conferred_file_name')){
            $conferred_status_file=$request->get('conferred_file_name');
        }

        if ($request->hasFile('contribution_file')) {
            $file = $request->file('contribution_file');   
            $contribution_file= $this->AddInDigiOcean($file);
        }else if($request->has('contribution_file_name')){
            $contribution_file=$request->get('contribution_file_name');
        }

        if ($request->hasFile('composition_file')) {
            $file = $request->file('composition_file');   
            $composition_file= $this->AddInDigiOcean($file);
        }else if($request->has('composition_file_name')){
            $composition_file=$request->get('composition_file_name');
        }
        if ($request->hasFile('uploaded_minutes')) {
            $file = $request->file('uploaded_minutes');   
            $uploaded_minutes= $this->AddInDigiOcean($file);
        }else if($request->has('uploaded_minutes_name')){
            $uploaded_minutes=$request->get('uploaded_minutes_name');
        }
        if ($request->hasFile('action_chalked_out_file')) {
            $action_chalked_out_file= $file = $request->file('action_chalked_out_file');   
            $this->AddInDigiOcean($file);
        }else if($request->has('action_file_name')){
            $action_chalked_out_file=$request->get('action_file_name');
        }
     
        $data=[
            'institute_name' => $request->institute_name,
            'institute_head_name' => $request->institute_head_name,
            'designation' => $request->designation,
            'institute_func_campus' => $request->institute_func_campus, 
            'princ_phno' => $request->princ_phno, 
            'princ_alternate_phno' => $request->princ_alternate_phno, 
            'princ_mobile' => $request->princ_mobile, 
            'princ_reg_email' => $request->princ_reg_email, 
            'address' => $request->address, 
            'city_town' => $request->city_town, 
            'state_ut' => $request->state_ut, 
            'pin_code' => $request->pin_code, 
            'confirm_autonomous_date' => \Carbon\Carbon::parse($request->confirm_autonomous_date)->format('Y-m-d'), 
            'type_institute' => $request->type_institute, 
            'location' => $request->location,
            'financial_status' => $request->financial_status, 
            'IQAC_director_name' => $request->IQAC_director_name, 
            'phone_no' => $request->phone_no, 
            'mobile_no' => $request->mobile_no, 
            'IQAC_email' => $request->IQAC_email, 
            'web_add_link_AQAR' => $request->web_add_link_AQAR, 
            'academic_calendar' => $request->academic_calendar, 
            'institute_weblink' => $request->institute_weblink, 
            'accrediation_details' => $request->accrediation_details, 
            'IQAC_establish_date' => \Carbon\Carbon::parse($request->IQAC_establish_date)->format('Y-m-d'), 
            'institute_assurance' => $request->institute_assurance, 
            'assurance_file' => isset($assurance_file) ? $assurance_file : $request->assurance_file, 
            'special_conferred_status' => $request->special_conferred_status, 
            'conferred_status_file' => $request->conferred_status_file,
            'IQAC_composition' => $request->IQAC_composition, 
            'composition_file' => isset($composition_file) ? $composition_file : $request->composition_file, 
            'no_IQAC_meeting' => $request->no_IQAC_meeting, 
            'minutes_IQAC_meeting' => $request->minutes_IQAC_meeting, 
            'uploaded_minutes' => isset($uploaded_minutes) ? $uploaded_minutes : $request->uploaded_minutes, 
            'IQAC_recive_fund' => $request->IQAC_recive_fund, 
            'fund_amt' => $request->fund_amt, 
            'fund_year' => $request->fund_year, 
            'IQAC_significant_contribution' => $request->IQAC_significant_contribution, 
            'contribution_file' =>isset($contribution_file) ? $contribution_file : $request->contribution_file, 
            'action_chalked_out' => $request->action_chalked_out, 
            'action_chalked_out_file' => isset($action_chalked_out_file) ? $action_chalked_out_file : $request->action_chalked_out_file, 
            'AQAR_placed_statutory' => $request->AQAR_placed_statutory, 
            'statutory_name' => $request->statutory_name, 
            'statutory_date' => \Carbon\Carbon::parse($request->statutory_date)->format('Y-m-d'), 
            'NAAC_or_other' => $request->NAAC_or_other, 
            'submitted_AISHE' => $request->submitted_AISHE,
            'year_submission' => $request->year_submission,
            'date_submission' => \Carbon\Carbon::parse($request->date_submission)->format('Y-m-d'),
            'management_info' => $request->management_info,
            'brief_desc' => $request->brief_desc,
            'sub_institute_id'=> $sub_institute_id,
        ];
        if($id==''){
            $data['created_at'] = now();
            return naac_partA1::insert($data);
        }else{
            $data['updated_at'] = now();            
            return naac_partA1::where('id',$id)->update($data);
        }
    }
    // Fees Type Report - Search criteria not works
    public function create(Request $request)
    {
        $type=$request->type;
        $sub_institute_id= session()->get('sub_institute_id');
        $res['partA2'] = DB::table('naac_part_a2')->where('sub_institute_id',$sub_institute_id)->first();
        // echo "<pre>";print_r($res['partA2']);exit;
        $res['multidisciplinary']=[
            "Delineate the vision/plan of institution to transform itself into a holistic multidisciplinary institution.",
            "Delineate the Institutional approach towards the integration of humanities and science with STEM and provide the detail of programs with combinations.",
            "Does the institution offer flexible and innovative curricula that includes credit-based courses and projects in the areas of community engagement and service, environmental education, and value-based towards the attainment of a holistic and multidisciplinary education. Explain",
            "What is the institutional plan for offering a multidisciplinary flexible curriculum that enables multiple entry and exits at the end of 1st, 2nd and 3rd years of undergraduate education while maintaining the rigor of learning? Explain with examples.",
            "What are the institutional plans to engage in more multidisciplinary research endeavours to find solutions to society's most pressing issues and challenges?",
            "Describe any good practice/s of the institution to promote Multidisciplinary / interdisciplinary approach in view of NEP 2020."
        ];

        $res['academic_bank']=[
            "Describe the initiatives taken by the institution to fulfil the requirement of Academic bank of credits as proposed in NEP 2020.",
            "Whether the institution has registered under the ABC to permit its learners to avail the benefit of multiple entries and exit during the chosen programme? Provide details.",
            "Describe the efforts of the institution for seamless collaboration, internationalization of education, joint degrees between Indian and foreign institutions, and to enable credit transfer.",
            "How faculties are encouraged to design their own curricular and pedagogical approaches within the approved framework, including textbook, reading material selections, assignments, and assessments etc.",
            "Describe any good practice/s of the institution pertaining to the implementation of Academic bank of credits (ABC) in the institution in view of NEP 2020."
        ];
        $res['skill_development']=[
            "Describe the efforts made by the institution to strengthen the vocational education and soft skills of students in alignment with National Skills Qualifications Framework",
            "Provide the details of the programmes offered to promote vocational education and its integration into mainstream education.",
            "How the institution is providing Value-based education to inculcate positivity amongst the learner that include the development of humanistic, ethical, Constitutional, and universal human values of truth (satya), righteous conduct (dharma), peace (shanti), love (prem), nonviolence (ahimsa), scientific temper, citizenship values, and also life-skills etc.",
            "Enlist the institution’s efforts to:",
            "Describe any good practice/s of the institution pertaining to the Skill development in view of NEP 2020."
        ];
        $res['enlist_skill'] = [
            "Design a credit structure to ensure that all students take at least one vocational course before graduating.",
            "Engaging the services of Industry veterans and Master Crafts persons to provide vocational skills and overcome gaps vis-à-vis trained faculty provisions.",
            "To offer vocational education in ODL/blended/on-campus modular modes to Learners.",
            "NSDC association to facilitate all this by creating a unified platform to manage learner enrolment (students and workers), skill mapping, and certification.",
            "Skilling courses are planned to be offered to students through online and/or distance mode."
        ];

        $res['appropriate_integration_indian'] =[
            "Delineate the strategy and details regarding the integration of the Indian Knowledge system (teaching in Indian Language, culture etc,) into the curriculum using both offline and online courses.",
            "What are the institutions plans to train its faculties to provide the classroom delivery in bilingual mode (English and vernacular)? Provide the details.",
            "Provide the details of the degree courses taught in Indian languages and bilingually in the institution.",
            "Describe the efforts of the institution to preserve and promote the following:",
            "Describe any good practice/s of the institution pertaining to the appropriate integration of Indian Knowledge system (teaching in Indian Language, culture, using online course) in view of NEP 2020."
        ];

        $res['sub_appropriate_integration_indian'] = [
            "Indian languages (Sanskrit, Pali, Prakrit and classical, tribal and endangered etc.)",
            "Indian ancient traditional knowledge",
            "Indian Arts",
            "Indian Culture and traditions."
        ];
        $res['focus_outcome']=[
            "Describe the institutional initiatives to transform its curriculum towards Outcome based Education (OBE)?",
            "Explain the efforts made by the institution to capture the Outcome based education in teaching and learning practices.",
            "Describe any good practice/s of the institution pertaining to the Outcome based education (OBE) in view of NEP 2020.",
        ];
        $res['online_education']=[
            "Delineate the possibilities of offering vocational courses through ODL mode in the institution.",
            "Describe about the development and use of technological tools for teaching learning activities. Provide the details about the institutional efforts towards the blended learning.",
            "Describe any good practice/s of the institution pertaining to the Distance education/online education in view of NEP 2020."
        ];
        // echo "<pre>";print_r($res['partA1']);exit;
        return is_mobile($type, "sqaa/add_partA2", $res, "view");
    }

    public function naacPartA2(Request $request){
        $type=$request->type;
        $sub_institute_id=session()->get('sub_institute_id');
        $InsertUpdate = $this->insertOrUpdate2($request,$sub_institute_id,'');
        $res['status_code']=1;
        $res['message']="Inserted Successfully";
        return is_mobile($type, "naac_parts.create", $res, "redirect");
    }

    public function naacPartA2Update(Request $request,$id){
        $type=$request->type;        
        $sub_institute_id=session()->get('sub_institute_id');
        $InsertUpdate = $this->insertOrUpdate2($request,$sub_institute_id,$id);
        $res['status_code']=1;
        $res['message']="Updated Successfully";
        return is_mobile($type, "naac_parts.create", $res, "redirect");
    }
    
    public function insertOrUpdate2($request,$sub_institute_id,$id){
        $data = [
            "multidisciplinary_head"=>$request->multidisciplinary,
            "multidisciplinary_data"=>$request->multidisciplinary_data,
            "academic_bank_head"=>$request->academic_bank,
            "academic_bank_data"=>$request->academic_bank_data,
            "skill_development_head"=>$request->skill_development,
            "skill_development_sub_head"=>$request->skill_development_sub_head,
            "skill_development_data"=>$request->skill_development_data,
            "appropriate_integration_head"=>$request->appropriate_integration_indian,
            "appropriate_integration_sub_head"=>$request->appropriate_integration_sub_head,
            "appropriate_integration_data"=>$request->appropriate_integration_indian_data,
            "focus_outcome_head"=>$request->focus_outcome,
            "focus_outcome_data"=>$request->focus_outcome_data,
            "online_education_head"=>$request->online_education,
            "online_education_data"=>$request->online_education_data,
            "sub_institute_id"=>$sub_institute_id,
        ];
        if($id==''){
            $data['created_at'] = now();
            return DB::table('naac_part_a2')->insert($data);
        }else{
            $data['updated_at'] = now();            
            return DB::table('naac_part_a2')->where('id',$id)->update($data);
        }
    }

    public function naacPartA3(Request $request){
        $type=$request->type;
        $sub_institute_id= session()->get('sub_institute_id');
        $res['partA3'] = DB::table('naac_part_a3')->where('sub_institute_id',$sub_institute_id)->first();
        return is_mobile($type, "sqaa/add_partA3", $res, "view");        
    }

    public function naacPartA3Store(Request $request){
        $type=$request->type;
        $sub_institute_id=session()->get('sub_institute_id');
        $InsertUpdate = $this->insertOrUpdate3($request,$sub_institute_id,'');
        $res['status_code']=1;
        $res['message']="Inserted Successfully";
        return is_mobile($type, "naac_parts3.index", $res, "redirect");
    }
    
    public function naacPartA3Update(Request $request,$id){
        $type=$request->type;        
        $sub_institute_id=session()->get('sub_institute_id');
        $InsertUpdate = $this->insertOrUpdate3($request,$sub_institute_id,$id);
        $res['status_code']=1;
        $res['message']="Updated Successfully";
        return is_mobile($type, "naac_parts3.index", $res, "redirect");
    }
    
    public function insertOrUpdate3($request,$sub_institute_id,$id){
        $program =$request->program_year.'||'.$request->program_number;
        $student_1=$request->student_1_year.'||'.$request->student_1_number;
        $student_2=$request->student_2_year.'||'.$request->student_2_number;
        $student_3 = $request->student_3_year.'||'.$request->student_3_number;
        $academic_1=$request->academic_1_year.'||'.$request->academic_1_number;
        $academic_2=$request->academic_2_year.'||'.$request->academic_2_number;
        $academic_3=$request->academic_3_year.'||'.$request->academic_3_number;
        $institution_1 =$request->institution_1_year.'||'.$request->institution_1_number;
        $institution_4 =$request->institution_4_year.'||'.$request->institution_4_number;

        $data=[
            'program'=> $program,
            'student_1'=> $student_1,
            'student_2'=> $student_2,
            'student_3'=> $student_3,
            'academic_1'=> $academic_1,
            'academic_2'=> $academic_2,
            'academic_3'=> $academic_3,
            'institution_1'=>$institution_1,
            'institution_2'=> $request->institution_2_number,
            'institution_3'=> $request->institution_3_number,
            'institution_4'=> $institution_4,
            'sub_institute_id'=>$sub_institute_id
        ];

        if($id==''){
            $data['created_at'] = now();
            return DB::table('naac_part_a3')->insert($data);
        }else{
            $data['updated_at'] = now();            
            return DB::table('naac_part_a3')->where('id',$id)->update($data);
        }
    }
}
