<?php

namespace App\Http\Controllers\sqaa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use function App\Helpers\is_mobile;
use App\Models\sqaa\sqaa_master;
use App\Models\sqaa\sqaa_mark;
use App\Models\sqaa\sqaa_document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Response;
use DB;
use PDF;

class sqaa_controller extends Controller
{
    //
    public function index()
    {
        $type="";
        $res['level_1'] = sqaa_master::where(['parent_id'=>0,"level"=>1])->orderBy('sort_order')->get()->toArray();
        // echo "<pre>";print_r($tabs);exit;
        return is_mobile($type, "sqaa/show", $res, "view");
    }

    public function create(Request $request){
        // echo "<pre>";print_r($request->all());exit;
        if($request->get('level_3')!=''){
            $menu_id = $request->get('level_3');
        }else if($request->get('level_2')!=''){
            $menu_id = $request->get('level_2');
        }        
        else if($request->get('level_1')!=''){
            $menu_id = $request->get('level_1');
        }
        else{
            $menu_id = 0;   
        }
        // echo "<pre>";print_r($request->all());exit;
        $type="";
        $sub_institute_id = session()->get('sub_institute_id');
         
        $res['level_1'] = sqaa_master::where(['level'=>1])->get()->toArray();
        $res['level_2_val']=sqaa_master::where(['level'=>2,'parent_id'=>$request->get('tabs_id')])->get()->toArray();
        $res['level_3_val']=sqaa_master::where(['level'=>3,'parent_id'=>$request->get('sel_level_1')])->get()->toArray();
        $res['level_4_val']=sqaa_master::where(['level'=>4,'parent_id'=>$request->get('sel_level_2')])->get()->toArray();
      
        $res['selected_1']=$request->get('tabs_id');
        $res['selected_2']=$request->get('sel_level_1');
        $res['selected_3']=$request->get('sel_level_2');
        $res['selected_4']=$request->get('sel_level_3');     
        // DB::enableQueryLog();
        $res['document'] = DB::table('sqaa_documant_master AS sdm')
        ->select('sdm.id as document_id', 'sdm.menu_id', 'sdm.title', 'sd.availability', 'sd.file')
        ->leftJoin('sqaa_documents AS sd', function ($join) use($menu_id,$sub_institute_id) {
            $join->on('sd.document_id', '=', 'sdm.id')
                ->where('sdm.menu_id', '=', $menu_id)
                ->where('sd.sub_institute_id', '=', $sub_institute_id);
        })
        ->where('sdm.menu_id', '=', $menu_id)
        ->get();

        $res['editor_text'] = DB::table('naac_editor_details AS sdm')
        ->where('sdm.menu_id', '=', $menu_id)
        ->where('sdm.sub_institute_id', '=', $sub_institute_id)
        ->first();

        $res['level_1_1']=$request->get('level_1');
        $res['level_2']=$request->get('level_2');
        $res['level_3']=$request->get('level_3');
        $res['level_4']=$request->get('level_4');
        $res['text_1']=$request->get('text_1');
        $res['text_2']=$request->get('text_2');
        $res['text_3']=$request->get('text_3');
        $res['text_4']=$request->get('text_4');
        $res['editor']=1;
       
        return is_mobile($type, "sqaa/show", $res, "view");
    
    }
    public function get_level(Request $request)
    {
        # code...
        if(isset($request->level_2)){
            $level_2 = sqaa_master::where(['parent_id'=>$request->level_2,"level"=>2])->orderBy('sort_order')->get()->toArray();
            return $level_2;
        }
       
        if(isset($request->level_3)){
            $level_3 = sqaa_master::where(['parent_id'=>$request->level_3,"level"=>3])->orderBy('sort_order')->get()->toArray();
            return $level_3;
        }
        if(isset($request->current_level)){
            $current_level = sqaa_master::where(['parent_id'=>$request->current_level])->orderBy('sort_order')->get()->toArray();
            return $current_level;
        }
    }

    public function store(Request $request){
        // echo "<pre>";print_r($request->all());exit;
        $type=$request->type;
        $sub_institute_id = session()->get('sub_institute_id');
        $user_id = session()->get('user_id');

        if($request->lev_1 != '' && $request->lev_2 != '' && $request->lev_3 != '' && $request->lev_4 != ''){
            $menu_id = $request->lev_4;
        } else  if($request->lev_1 != '' && $request->lev_2 != '' && $request->lev_3 != ''){
            $menu_id = $request->lev_3;
        }else if($request->lev_1 != '' && $request->lev_2 != ''){
            $menu_id = $request->lev_2;

        }else if($request->lev_1 != ''){
            $menu_id = $request->lev_1;
        }else{
            $menu_id = 0;
        }
        // echo "<pre>";print_r($request->all());exit;

        if(isset($request->naa_details) && $request->naa_details!=''){
            $check_data = DB::table('naac_editor_details')->where('menu_id',$menu_id)->where('sub_institute_id',$sub_institute_id)->get()->toArray();
            if(!empty($check_data)){
                $update=DB::table('naac_editor_details')->where('menu_id',$menu_id)->where('sub_institute_id',$sub_institute_id)->update([
                    'menu_id'=>$menu_id,
                    'details'=>$request->naa_details,
                    'sub_institute_id'=>$sub_institute_id,
                    'updated_at'=>now()
                ]);
            }else{
                 $insert=DB::table('naac_editor_details')->insert([
                    'menu_id'=>$menu_id,
                    'details'=>$request->naa_details,
                    'sub_institute_id'=>$sub_institute_id,
                    'created_at'=>now()
                    ]);
            }
        }

        $arr = [
            "menu_id"=>$menu_id,
            "mark"=>$request->mark,
            "created_by" => $user_id,
            "sub_institute_id" => $sub_institute_id,
        ];
        $check_data=$this->check_data($arr,"sqaa_marks");
        if(!$check_data){
        $data = new sqaa_mark();
        $data->menu_id=$menu_id;
        $data->mark=$request->mark ?? 0;
        $data->created_by = $user_id;
        $data->sub_institute_id = $sub_institute_id;
        $data->created_at = now();
        $data->save();
        }
            $res['status_code']=1;
            $res['message']="Data inserted";
            if (!empty($request->get('document'))) {
                for ($i = 0; $i < count($request->get('document')); $i++) {
                    $documentData = [
                        'document' => $request->get('document')[$i],
                    ];
        // echo "<pre>";print_r($request->file('files'));exit;
                    
                    $document = $request->get('document')[$i];
                    $doc_id = $request->get('doc_id')[$i];
                    $reasons = $request->get('reasons')[$i];
                    $availability = $request->get('availability')[$i] ?? 'no';
                    $file_have = '1=1';
                    $filename = isset($request->get('doc_files')[$i]) ? $request->get('doc_files')[$i]->getClientOriginalName() : null;

                    // Check if a file is present for this row
                    $availability = $request->get('availability')[$i] ?? 'no';
                    $filename = isset($request->get('doc_files')[$i]) ? $request->file('doc_files')[$i]->getClientOriginalName() : null;
            
                    // Check if a file is present for this row
                    if ($availability == "yes" && isset($request->doc_files[$i]) && $request->file('doc_files')[$i]->isValid()) {
                        $file = $request->file('doc_files')[$i];
                        $filename = $sub_institute_id.'_'.$i.'_'.$file->getClientOriginalName();
                        Storage::disk('digitalocean')->putFileAs('public/naac/', $file, $filename, 'public');
                    } else {
                        if(isset($request->get('update_file')[$i]) && $availability == "yes"){
                            $filename=$request->get('update_file')[$i];
                        }else if (isset($request->get('update_file')[$i])){
                            $filename=$request->get('update_file')[$i];
                            $file_path = 'public/naac/' . $filename;
                            if (Storage::disk('digitalocean')->exists($file_path)) {
                                Storage::disk('digitalocean')->delete($file_path);
                                if (!Storage::disk('digitalocean')->exists($file_path)) {
                                    $filename=null;
                                }   
                            } 
                        }else{
                            $filename=null;
                        }
                    }
                $doc_arr=[
                    "menu_id"=>$menu_id,
                    "document_id"=>$doc_id,
                    "created_by" => $user_id,
                    "sub_institute_id" => $sub_institute_id,                    
                ];
                $check_doc_data=$this->check_data($doc_arr,"sqaa_documents");
                // echo "<pre>";print_r($check_doc_data);
                if(!$check_doc_data){
                $data_doc = new sqaa_document();
                $data_doc->menu_id=$menu_id;
                $data_doc->title=$document;
                $data_doc->document_id=$doc_id;
                $data_doc->reasons=$reasons;                
                $data_doc->availability=$availability;
                $data_doc->file=$filename;        
                $data_doc->created_by = $user_id;
                $data_doc->sub_institute_id = $sub_institute_id;
                $data_doc->created_at = now();
                $data_doc->save();
                }else{
             
                $data_doc = sqaa_document::where($doc_arr)->update([
                    "title"=>$document,
                    "document_id"=>$doc_id,
                    "reasons"=>$reasons,               
                    "availability"=>$availability,
                    "file"=>$filename,    
                    "created_by"=> $user_id,
                    "sub_institute_id"=> $sub_institute_id,
                    "updated_at"=> now(),
                ]);
                }
            }
            // exit;
        }else{
                $res['status_code']=0;
                $res['message']="Document not inserted";
        }
       
        return is_mobile($type, "naac_master.index", $res);
        // return $request;
    }

    function check_data($request,$table){
        $check_table_data = DB::table($table)->where($request)->get()->toArray();
        return $check_table_data;
    }

    public function edit_gen_pdf(Request $request) {
        $res='';
        $this->generatePdf($request);
        $type='';
        // return is_mobile($type, "sqaa/generatePdf", $res, "view");
        return redirect()->back();
    }

    public function generatePdf(Request $request) {
        $sub_institute_id = session()->get('sub_institute_id');
        $htmlContent = $request->get('html_content');
        $menu_id = $request->get('menu_id_pdf');
        $doc_id = $request->get('doc_id_pdf');
        
        $pdf = PDF::loadHTML($htmlContent);
        $filename = $sub_institute_id.'_pdf_menu'.$menu_id.'_doc'.$doc_id.'.pdf';
        $filePath= 'sqaa/' . $filename;
        $pdf->save(public_path('sqaa/' . $filename));
        
        $fileUrl = asset('sqaa/' . $filename);
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
    
        // Return the PDF file as a response
        return response()->file($filePath, $headers);
        // return redirect()->route('gen-pdf', ['text' => $res['text'], 'path' => $res['path']]);
        // return $request;exit;
    }   

  public function unlink_file(Request $request){
        if (file_exists($request->file)) {
            if (unlink($request->file)) {
                echo 'File deleted successfully.';
            } else {
                echo 'Failed to delete the file.';
            }
        } else {
            echo 'File not found.';
        }
  }

  // INSERT INTO SQAA MASTER TABLE
  public function InsertNaccMasterView(Request $request){
    // $res ='';
    $type=$request->type;
    $res['get_tabs']=DB::table('sqaa_master')->where('level',1)->where('parent_id',0)->get()->toArray();
    // echo "<pre>";print_r($get_tabs);exit;
    return is_mobile($type,'sqaa/insertNaacMaster',$res,'view');
  }

    public function InsertNaccMaster(Request $request){
        $type=$request->type;
        $title = $request->title;
        $description = $request->description;
        $level = 1;
        $created_by=session()->get('user_id');
        $parent_id=$request->parent_id;

       if(isset($request->lev_2) && $request->lev_2 !=0){
            $parent_id=$request->lev_2;
            $level = 4;
        }elseif(isset($request->lev_1) && $request->lev_1 !=0){
            $parent_id=$request->lev_1;
            $level = 3;
        }else if(isset($request->tabs_id) && $request->tabs_id !=0){
            $parent_id=$request->tabs_id;
            $level = 2;
        }

        $sort_order = DB::table('sqaa_master')->where('level',$level)->when($parent_id,function($q) use($parent_id){
                        $q->where('parent_id',$parent_id);
                    })->orderBy('id','desc')->first();

        $check_data = DB::table('sqaa_master')->where('title',$title)->where('description',$description)->where('level',$level)
        ->when($parent_id,function($q) use($parent_id){
                        $q->where('parent_id',$parent_id);
                    })->first();

        if(empty($check_data)){
            $insert = DB::table('sqaa_master')->insert([
                'title'=>$title,
                'description'=>$description,
                'parent_id'=>$parent_id ?? 0,
                'level'=>$level,
                'status'=>1,
                'sort_order'=>isset($sort_order->sort_order) ? ($sort_order->sort_order+1) : 1,
                'sub_institute_id'=>0,
                'created_by'=>$created_by,
                'created_at'=>now(),
            ]);
            $res['status_code']=1;
            $res['message']= $title.'Inserted successfully';
        }else{
         $update = DB::table('sqaa_master')->where('title',$title)->where('description',$description)->where('level',$level)->when($parent_id,function($q) use($parent_id){
                        $q->where('parent_id',$parent_id);
                    })->update([
                'title'=>$title,
                'description'=>$description,
                'parent_id'=>$parent_id ?? 0,
                'level'=>$level,
                'status'=>1,
                'sort_order'=>$check_data->sort_order,
                'sub_institute_id'=>0,
                'created_by'=>$created_by,
                'updated_at'=>now(),
            ]);
        $res['status_code']=1;
        $res['message']= $title.'Updated successfully';

        }
        // echo "<pre>";print_r($request);exit;
        return is_mobile($type, "insert_naac_master.index", $res);
    }

     // INSERT INTO Document master table
  public function DocTitleMasterView(Request $request){
    // $res ='';
    $type=$request->type;
    $res['get_tabs']=DB::table('sqaa_master')->where('level',1)->where('parent_id',0)->get()->toArray();
    // echo "<pre>";print_r($get_tabs);exit;
    return is_mobile($type,'sqaa/documentTitleInsert',$res,'view');
  }

    public function DocTitleMaster(Request $request){
        $type=$request->type;
        $title = $request->title;
        $created_by=session()->get('user_id');
        $menu_id=$request->parent_id;
        $sub_institute_id= session()->get('sub_institute_id');
       if(isset($request->lev_2) && $request->lev_2 !=0){
            $menu_id=$request->lev_2;
        }elseif(isset($request->lev_1) && $request->lev_1 !=0){
            $menu_id=$request->lev_1;
        }else if(isset($request->tabs_id) && $request->tabs_id !=0){
            $menu_id=$request->tabs_id;
        }

        $check_data = DB::table('sqaa_documant_master')->where('title',$title)->where('sub_institute_id',$sub_institute_id)->when($menu_id,function($q) use($menu_id){
                        $q->where('menu_id',$menu_id);
                    })->first();

        if(empty($check_data)){
            $insert = DB::table('sqaa_documant_master')->insert([
                'title'=>$title,
                'menu_id'=>$menu_id ?? 0,
                'sub_institute_id'=>$sub_institute_id,
                'created_at'=>now(),
            ]);
            $res['status_code']=1;
            $res['message']= $title.'Inserted successfully';
        }else{
         $update = DB::table('sqaa_documant_master')->where('title',$title)->where('sub_institute_id',$sub_institute_id)->when($menu_id,function($q) use($menu_id){
                        $q->where('menu_id',$menu_id);
                    })->update([
               'title'=>$title,
                'menu_id'=>$menu_id ?? 0,
                'sub_institute_id'=>$sub_institute_id,
                'updated_at'=>now(),
            ]);
        $res['status_code']=1;
        $res['message']= $title.'Updated successfully';

        }
        // echo "<pre>";print_r($request);exit;
        return is_mobile($type, "naac_doc_master.index", $res);
    }

    public function naac_details(){
        
    }

}
