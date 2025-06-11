@extends('layout')
@section('container')
<link href="{{ asset('/plugins/bower_components/summernote/dist/summernote.css') }}" rel="stylesheet" />
<style>
	.note-children-containe,
	.note-image-popover,
	.note-table-popover,
	.note-popover.popover,
	.note-popover .popover-content {
		display: none !important;
	}
th{
	width:50% !important;
}
th>div{
	margin-bottom:0px !important;
}
.headings{
	color:#5c5cd8;
	font-size:1.3rem;
	padding:20px;
}
</style>
<div id="page-wrapper">
	<div class="container-fluid">
		<div class="row bg-title">
			<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
				<h4 class="page-title">IQAC Part A2</h4>
			</div>
		</div>
        <!-- // data card  -->
        <div class="card">
			@if ($sessionData = Session::get('data')) 
                @if($sessionData['status_code'] == 1)
                <div class="alert alert-success alert-block">
                @else
                <div class="alert alert-danger alert-block">
                @endif
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <strong>{{ $sessionData['message'] }}</strong>
                    </div>
                @endif
                <!-- form div start  -->
                <div class="col-md-12">
                <form action="@if(!empty($data['partA2'])){{ route('naac_parts2.update', $data['partA2']->id) }}@else {{route('naac_parts2.store')}} @endif" enctype="multipart/form-data" method="post">
                @if(!empty($data['partA2']))
							{{ method_field("PUT") }}
							@else
							{{ method_field("POST") }}							
						@endif
				
                @csrf
                <!-- multidisciplinary -->
                    <div class="form-group d-flex justify-space-evenly">
                        <div class="col-md-6">
                            <label for="multidisciplinary">1. Multidisciplinary / interdisciplinary:</label>
                            <select class="form-control" name="multidisciplinary" id="multidisciplinary" onchange="get_chat('multidisciplinary','')" required>
                            <option value="">Please Select</option>
                                @foreach($data['multidisciplinary'] as $key => $value)
                                    <option data-key="{{$key}}" @if(isset($data['partA2']->multidisciplinary_head) && $data['partA2']->multidisciplinary_head==$value) selected
								 	@endif>{{$value}}</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="col-md-6">
                        <textarea class="summernote" id="multidisciplinary_data" name="multidisciplinary_data" required>
									@if(isset($data['partA2']->multidisciplinary_data)){!! $data['partA2']->multidisciplinary_data !!}
								 	@endif
						</textarea>
                        </div>
                    </div>
                    <!-- academic_bank -->
                      <div class="form-group d-flex justify-space-evenly">
                        <div class="col-md-6">
                            <label for="academic_bank">2. Academic bank of credits (ABC):</label>
                            <select class="form-control" name="academic_bank" id="academic_bank" onchange="get_chat('academic_bank','')" required>
                            <option value="">Please Select</option>
                                @foreach($data['academic_bank'] as $key=>$value)
                                <option data-key="{{$key}}" @if(isset($data['partA2']->academic_bank_head) && $data['partA2']->academic_bank_head==$value) selected
								 	@endif>{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                        <textarea class="summernote" id="academic_bank_data" name="academic_bank_data" required>
						@if(isset($data['partA2']->academic_bank_data)){!! $data['partA2']->academic_bank_data !!}@endif
						</textarea>
                        </div>
                    </div>
                     <!-- skill_development -->
                     <div class="form-group d-flex justify-space-evenly">
                        <div class="col-md-6">
                            <label for="skill_development">3. Skill development:</label>
                            <select class="form-control mb-2" name="skill_development" id="skill_development" onchange="get_chat('skill_development','enlist_skill')" required>
                            <option value="">Please Select</option>
                                @foreach($data['skill_development'] as $key=>$value)
                                <option data-key="{{$key}}" @if(isset($data['partA2']->skill_development_head) && $data['partA2']->skill_development_head==$value) selected @endif>{{$value}}</option>
                                @endforeach
                            </select>

                            <input type="hidden" name="skill_development_sub_head" id="skill_development_sub_head" @if(isset($data['partA2']->skill_development_sub_head)) value="{{$data['partA2']->skill_development_sub_head}}" @endif>

                            <div class="enlist_skill_div" id="enlist_skill_div" >
                            <label for="enlist_skill">Enlist the institution’s efforts to:</label>
                            <select class="form-control" name="skill_development_sub" id="enlist_skill" onchange="get_chat('enlist_skill','')" @if(isset($data['partA2']->skill_development_sub_head) && $data['partA2']->skill_development_sub_head==$value) selected @endif>
                            
                                @foreach($data['enlist_skill'] as $key=>$value)
                                <option data-key="{{$key}}"  @if(isset($data['partA2']->skill_development_sub_head) && $data['partA2']->skill_development_sub_head==$value) selected @endif>{{$value}}</option>
                                @endforeach
                            </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                        <textarea class="summernote" id="skill_development_data" name="skill_development_data" required>
									@if(isset($data['partA2']->skill_development_data)){!! $data['partA2']->skill_development_data !!}
								 	@endif
						</textarea>
                        </div>
                    </div>
                     <!-- appropriate_integration_indian -->
                     <div class="form-group d-flex justify-space-evenly">
                        <div class="col-md-6">
                            <label for="appropriate_integration_indian">4. Appropriate integration of Indian Knowledge system (teaching in Indian Language, culture, using online course)</label>
                            <select class="form-control mb-2" name="appropriate_integration_indian" id="appropriate_integration_indian" onchange="get_chat('appropriate_integration_indian','sub_appropriate_integration_indian')" required >
                            <option value="">Please Select</option>
                                @foreach($data['appropriate_integration_indian'] as $key=>$value)
                                <option data-key="{{$key}}" @if(isset($data['partA2']->appropriate_integration_head) && $data['partA2']->appropriate_integration_head==$value) selected @endif>{{$value}}</option>
                                @endforeach
                            </select>

                            <input type="hidden" name="appropriate_integration_sub_head" id="appropriate_integration_sub_head" @if(isset($data['partA2']->appropriate_integration_sub_head)) value="{{$data['partA2']->appropriate_integration_sub_head}}" @endif>

                            <div class="appropriate_integration_div" id="appropriate_integration_div" >
                            <label for="sub_appropriate_integration_indian">Describe the efforts of the institution to preserve and promote the following:</label>
                            <select class="form-control" name="appropriate_integration_indian_sub" id="sub_appropriate_integration_indian" onchange="get_chat('sub_appropriate_integration_indian','')">
                                @foreach($data['sub_appropriate_integration_indian'] as $key=>$value)
                                <option data-key="{{$key}}" @if(isset($data['partA2']->appropriate_integration_sub_head) && $data['partA2']->appropriate_integration_sub_head==$value) selected @endif>{{$value}}</option>
                                @endforeach
                            </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                        <textarea class="summernote" id="appropriate_integration_indian_data" name="appropriate_integration_indian_data" required>
						@if(isset($data['partA2']->appropriate_integration_data)){!! $data['partA2']->appropriate_integration_data !!}
								 	@endif
						</textarea>
                        </div>
                    </div>
                    <!-- focus_outcome -->
                    <div class="form-group d-flex justify-space-evenly">
                        <div class="col-md-6">
                            <label for="focus_outcome">5. Focus on Outcome based education (OBE):</label>
                            <select class="form-control" name="focus_outcome" id="focus_outcome" onchange="get_chat('focus_outcome','')" required>
                            <option value="">Please Select</option>
                                @foreach($data['focus_outcome'] as $key=>$value)
                                <option data-key="{{$key}}"  @if(isset($data['partA2']->focus_outcome_head) && $data['partA2']->focus_outcome_head==$value) selected @endif>{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                        <textarea class="summernote" id="focus_outcome_data" name="focus_outcome_data" required>
									@if(isset($data['partA2']->focus_outcome_data)){!! $data['partA2']->focus_outcome_data !!}
								 	@endif
						</textarea>
                        </div>
                    </div>
                    <!-- online_education -->
                    <div class="form-group d-flex justify-space-evenly">
                        <div class="col-md-6">
                            <label for="online_education">6. Distance education/online education:</label>
                            <select class="form-control" name="online_education" id="online_education" onchange="get_chat('online_education','')" required>
                            <option value="">Please Select</option>
                                @foreach($data['online_education'] as $key=>$value)
                                <option data-key="{{$key}}" @if(isset($data['partA2']->online_education_head) && $data['partA2']->online_education_head==$value) selected @endif>{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                        <textarea class="summernote" id="online_education_data" name="online_education_data" required>
									@if(isset($data['partA2']->focus_outcome_data)){!! $data['partA2']->online_education_data !!}
								 	@endif
						</textarea>
                        </div>
                    </div>
                    
                    </div>
                <div class="col-md-12">
                    <center>
                    <a class="btn btn-primary" href='{{route("naac_parts.index")}}'>Back</a>
                    @if(empty($data['partA2']))
                        <input type="submit" value="Save" class="btn btn-success">
                    @else
                        <input type="submit" value="Update" class="btn btn-success">
                        <a class="btn btn-primary" href='{{route("naac_parts3.index")}}'>Next</a>
                    @endif
                    </center>
				</div>	
                </form>
                </div>
                <!-- form div end  -->
                   
            </div>
        <!-- data card end  -->

    </div>
</div>
@include('includes.footerJs')
<script src="{{asset('/plugins/bower_components/summernote/dist/summernote.min.js')}}"></script>
<script>

$( document ).ready(function() { 
    @if(isset($data['partA2']))
    @if(empty($data['partA2']->skill_development_sub_head))
        $('#enlist_skill_div').hide();
    @endif
    @if(empty($data['partA2']->appropriate_integration_sub_head))    
        $('#appropriate_integration_div').hide();
    @endif
    @else
        $('#enlist_skill_div').hide();
        $('#appropriate_integration_div').hide();
    @endif

    
    $('.summernote').summernote({
        height: 150, // set editor height
        minHeight: null, // set minimum height of editor
        maxHeight: null, // set maximum height of editor
        focus: false // set focus to editable area after initializing summernote
    });

    $('[data-toggle="popover"]').popover({title: "",html: true});
    
    $('[data-toggle="popover"]').on('click', function (e) {
        $('[data-toggle="popover"]').not(this).popover('hide');
    });

});
function get_chat(select_id,sub_select_id){
    $('#'+select_id+'_data').summernote('code', 'Please Wait !!'); ;
    
    var sel_val = $('#' + select_id).val();
    var dataKey = $('#' + select_id + ' option:selected').attr('data-key');
    var sub_enlist =$('#skill_development_sub_head').val();
    var appropriate_integration =$('#appropriate_integration_sub_head').val();
    var message = $('#' + select_id).val();;
    if((dataKey==3 && sub_select_id=="enlist_skill") || select_id=="enlist_skill"){
        $('#enlist_skill_div').show();
        var sel_enlist = $('#enlist_skill').val(); 
        // alert(sel_enlist);       
        $('#skill_development_sub_head').val(sel_enlist); 
        var message = $('#skill_development_sub_head').val();
              
    }
     if((dataKey==3 && sub_select_id=="sub_appropriate_integration_indian")  || select_id=="sub_appropriate_integration_indian"){
        $('#appropriate_integration_div').show();
        var sel_aii = $('#sub_appropriate_integration_indian').val();                
        $('#appropriate_integration_sub_head').val(sel_aii);  
         var message = $('#appropriate_integration_sub_head').val();
    }

    if(select_id=="skill_development" && (dataKey!=3) && sub_enlist !=''){
        $('#enlist_skill_div').hide();
        $('#skill_development_sub_head').val('');       
    }
     if(select_id=="appropriate_integration_indian" && (dataKey!=3)  && appropriate_integration !=''){
        $('#appropriate_integration_div').hide();
        $('#appropriate_integration_sub_head').val('');       
    }
    
    // alert('#'+select_id+'_data');
    if(message!=''){
    $.ajax({
        type: 'GET',
        url: '{{route("chat")}}',
        data: {
            message: message
        },
        success: function(data) {
            $('#'+select_id+'_data').summernote('code', ''); 
            $('#'+select_id+'_data').summernote('code', data);            
        },
        error: function(error) {
            console.error(error);
        }
    });
    }
}
</script>
@include('includes.footer')
@endsection