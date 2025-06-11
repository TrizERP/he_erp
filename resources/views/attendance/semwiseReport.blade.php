@extends('layout')
@section('container')

<div id="page-wrapper">
    <div class="container-fluid">

    	<div class="row bg-title">
          	<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Semisterwiase Attendance Report</h4> </div>
            </div>
         </div>
 		@php
            $grade_id = $standard_id = $division_id = $from_date = $to_date ='';
             $batch =[];
 			 if(isset($data['from_date'])){
            	$from_date = $data['from_date'];
            }
            if(isset($data['to_date'])){
            	$to_date = $data['to_date'];
            }
            if(isset($data['batch'])){
            	$batch = $data['batch'];
            }
            if(isset($data['grade_id'])){
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            $att_type = ['Lecture','Lab','Tutorial'];
            
            $report_type = ['pw'=>'Percentage wise','nw'=>'Number of Lectures wise'];

            @endphp
         <div class="card">
         	<form action="{{route('semwise_report.create')}}">	
         		@csrf
         	<div class="row">
         		
        		{{ App\Helpers\SearchChain('2','single','grade,std,div',$grade_id,$standard_id,$division_id) }}
         		<div class="form-group col-md-2">
         			<label>Type</label>
         			<select class="form-control" name="att_type" id="att_type" required>
         				<option>Select</option>
         				@foreach($att_type as $key=>$value)
         				<option value="{{$value}}" @if(isset($data['attendance_type']) && $data['attendance_type']==$value) selected @endif>{{$value}}</option>
         				@endforeach
         			</select>
         		</div>

         		<div class="form-group col-md-2" id="batch_div">
         			<label>Batch</label>
         			<select class="form-control" name="batch" id="batch">
         				@if(!empty($batch))
         					@foreach($batch as $key => $value)
         					<option value="{{$value->id}}"  @if(isset($data['batch_id']) && $data['batch_id']==$value->id) selected @endif>{{$value->title}}</option>
         					@endforeach
         				@endif
         			</select>
         		</div>

         		<div class="form-group col-md-2">
         			<label>Report Type</label>
         			<select class="form-control" name="report_type">
         				@foreach($report_type as $key=>$value)
         				<option value="{{$key}}" @if(isset($data['report_type']) && $data['report_type']==$value) selected @endif>{{$value}}</option>
         				@endforeach
         			</select>
         		</div>

				<div class="form-group col-md-2">
         			<label>Below Percent(%)</label>
         			<input type="number" class="form-control" name="below_percent" @if(isset($data['below_percent'])) value="{{$data['below_percent']}}" @endif autocomplete="off">
				</div>

			<div class="form-group col-md-2">
			<label>From Date</label>
        	<input type="text" id="from_date" name="from_date" value="{{$from_date}}" class="form-control mydatepicker" autocomplete="off" required>
         	</div>

			<div class="form-group col-md-2">
				<label>To Date</label>
        		<input type="text" id="to_date" name="to_date" value="{{$to_date}}" class="form-control mydatepicker" autocomplete="off" required>
         	</div>

         	</div>

         	<div class="col-md-12 form-group">
                <center>
                    <input type="submit" name="submit" value="Search" class="btn btn-success">
                </center>
            </div>

         	</form>

         </div>

        @if(!empty($data['header']))
         <div class="card">
         	<div class="row">
         		 <div class="table-responsive">
                    <table id="example" class="table table-striped">
                        <thead>
                        	<tr>
                               <th>SR No</th>
                               <th>{{App\Helpers\get_string('grno','request')}}</th>
                               <th>{{App\Helpers\get_string('studentname','request')}}</th>
                                @php 
                               		$tot = 0;
                               		$i = 1;
                               @endphp
                               @foreach($data['header'] as $index => $value)
                               <th>{{$value->short_name}}({{$value->TOTAL_LEC}})</th>
                               @php 
                               	$tot += isset($value->TOTAL_LEC) ? $value->TOTAL_LEC : 0;
                               @endphp
                               @endforeach
                               <th>Total({{$tot}})</th>
                               <th class="text-left">%</th>
                            </tr>         
                        </thead>
                        <tbody>
                        	@if(isset($data['details']))
                                @foreach($data['details'] as $key => $val)
							    <tr>
							    	<td>{{$i++}}</td>
							        <td>{{$val['enrollment_no']}}</td>
							        <td>{{$val['student_name']}}</td>
							        @foreach($data['header'] as $index => $value)
							            <td>
							                @if(isset($val['COURSE_'.$value->subject_id]))
							                    {{$val['COURSE_'.$value->subject_id]}}
							                @else
							                    0
							                @endif
							            </td>
							        @endforeach
							       <td>{{$val['TOTAL']}}</td>
							       <td>{{$val['TOTAL_PERCENTAGE']}}</td>
							    </tr>
							@endforeach

                            @endif
                        </tbody>
                    </table>
                 </div>
         	</div>
         </div>
        @endif

  </div>
</div>
@include('includes.footerJs')

<script type="text/javascript">
	  $(document).ready(function () {

	  	$('#batch_div').hide();
	  	var selectVal = $('#att_type').val();

	  	var batchs = @json($data['batch'] ?? []); 
	  	var batch_id = '{{$data["batch_id"] ?? ""}}'; 

        if (batch_id && batchs.length > 0 && selectVal !="Lecture") {
            $('#batch_div').show();
        }

	  	$('#att_type').on('change',function(){
	  		var type=$(this).val();
	  		var standard=$('#standard').val();
	  		var division=$('#division').val();

	  		if(type!="Lecture"){
	  		$.ajax({
	  			 type: "GET",
	  			url:'/get-batch?standard='+standard+'&division='+division,
	  			success: function (res) {
	  				$('#batch_div').show();
	  				$('#batch').empty();
					if (res) {
						$.each(res, function (key, value) {
                            $("#batch").append('<option value="' + value.id + '">' + value.title + '</option>');
                        })
					}
	  			}

	  		})

	  		}else{
			  	$('#batch_div').hide();
			  }
	  	})
	  })

	  function checkAll(ele) {
	     var checkboxes = document.getElementsByTagName('input');
	     if (ele.checked) {
	         for (var i = 0; i < checkboxes.length; i++) {
	             if (checkboxes[i].type == 'checkbox') {
	                 checkboxes[i].checked = true;
	             }
	         }
	     } else {
	         for (var i = 0; i < checkboxes.length; i++) {
	             console.log(i)
	             if (checkboxes[i].type == 'checkbox') {
	                 checkboxes[i].checked = false;
	             }
	         }
	     }
	}
    function check_validation()
    {    
        var checked_questions = err = 0;

        $("input[name='students[]']:checked").each(function ()
        {             
            checked_questions = checked_questions + 1;
        });
        if(checked_questions == 0)
        {
            alert("Please Select Atleast one question in paper from search");
            err = 1;
            return false;
        }else{
            return true;
        }
    }
</script>
@include('includes.footer')

@endsection