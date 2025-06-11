@extends('layout')
@section('container')
<style>
th{
	width:50% !important;
}
</style>
<div id="page-wrapper">
	<div class="container-fluid">
		<div class="row bg-title">
			<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
				<h4 class="page-title">IQAC Part A3</h4>
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

                @php 
                    $program_1_year=$program_1_number=$student_1_year=$student_1_number=$student_2_year=$student_2_number=$student_3_year=$student_3_number=$academic_1_year=$academic_1_number=$academic_2_year=$academic_2_number=$academic_3_year=$academic_3_number=$institution_1_year=$institution_1_number=$institution_2=$institution_3=$institution_4_year=$institution_4_number = '';

                    if(!empty($data['partA3'])){

                        if(isset($data['partA3']->program)){
                            $program = explode('||',$data['partA3']->program);
                            $program_1_year=$program[0];
                            $program_1_number=$program[1];
                        }
                        if(isset($data['partA3']->student_1)){
                            $student_1 = explode('||',$data['partA3']->student_1);
                            $student_1_year=$student_1[0];
                            $student_1_number=$student_1[1];
                        }
                        if(isset($data['partA3']->student_2)){
                            $student_2 = explode('||',$data['partA3']->student_2);
                            $student_2_year=$student_2[0];
                            $student_2_number=$student_2[1];
                        }
                        if(isset($data['partA3']->student_3)){
                            $student_3 = explode('||',$data['partA3']->student_3);
                            $student_3_year=$student_3[0];
                            $student_3_number=$student_3[1];
                        }
                        if(isset($data['partA3']->academic_1)){
                            $academic_1 = explode('||',$data['partA3']->academic_1);
                            $academic_1_year=$academic_1[0];
                            $academic_1_number=$academic_1[1];
                        }
                        if(isset($data['partA3']->academic_2)){
                            $academic_2 = explode('||',$data['partA3']->academic_2);
                            $academic_2_year=$academic_2[0];
                            $academic_2_number=$academic_2[1];
                        }
                        if(isset($data['partA3']->academic_3)){
                            $academic_3 = explode('||',$data['partA3']->academic_3);
                            $academic_3_year=$academic_3[0];
                            $academic_3_number=$academic_3[1];
                        }

                        if(isset($data['partA3']->institution_1)){
                            $institution_1 = explode('||',$data['partA3']->institution_1);
                            $institution_1_year=$institution_1[0];
                            $institution_1_number=$institution_1[1];
                        }

                         if(isset($data['partA3']->institution_2)){
                            $institution_2 = $data['partA3']->institution_2;
                        }
                        if(isset($data['partA3']->institution_3)){
                            $institution_3 =$data['partA3']->institution_3;
                        }

                        if(isset($data['partA3']->institution_4)){
                            $institution_4 = explode('||',$data['partA3']->institution_4);
                            $institution_4_year=$institution_4[0];
                            $institution_4_number=$institution_4[1];
                        }
                        
                    }
                @endphp
                <!-- form div start  -->
                <form action="@if(!empty($data['partA3'])){{ route('naac_parts3.update', $data['partA3']->id) }}@else {{route('naac_parts3.store')}} @endif" enctype="multipart/form-data" method="post">
                <div class="col-md-12">
                
                @if(!empty($data['partA3']))
					{{ method_field("PUT") }}
				@else
					{{ method_field("POST") }}							
				@endif
				
                @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th colspan="2"><b>1. Programmes:</b></th>
                            </tr>
                            <tr>
                                <th><b>1.1 Number of programmes offered during the year:</b></th>
                                <th>
                                    <table style="width:100%">
                                        <tr>
                                            <th><b>Year</b></th>
                                            <td><input type="text" name="program_year" id="program_year" class="form-control" @if($program_1_year!='') value="{{$program_1_year}}" @endif></td>
                                        </tr>
                                        <tr>
                                            <th><b>Number</b></th>
                                            <td><input type="number" name="program_number" id="program_number" class="form-control" @if($program_1_number!='') value="{{$program_1_number}}" @endif></td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>


                            <tr>
                                <th colspan="2"><b>2. Students:</b></th>
                            </tr>
                            <tr>
                                <th><b>2.1 Total number of students during the year:</b></th>
                                <th>
                                    <table style="width:100%">
                                        <tr>
                                            <th><b>Year</b></th>
                                            <td><input type="text" name="student_1_year" id="student_1_year" class="form-control" @if($student_1_year!='') value="{{$student_1_year}}" @endif></td>
                                        </tr>
                                        <tr>
                                            <th><b>Number</b></th>
                                            <td><input type="number" name="student_1_number" id="student_1_number" class="form-control" @if($student_1_number!='') value="{{$student_1_number}}" @endif></td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                            <tr>
                                <th><b>2.2 Number of outgoing / final year students during the year:</b></th>
                                <th>
                                    <table style="width:100%">
                                        <tr>
                                            <th><b>Year</b></th>
                                            <td><input type="text" name="student_2_year" id="student_2_year" class="form-control" @if($student_2_year!='') value="{{$student_2_year}}" @endif></td>
                                        </tr>
                                        <tr>
                                            <th><b>Number</b></th>
                                            <td><input type="number" name="student_2_number" id="student_2_number" class="form-control" @if($student_2_number!='') value="{{$student_2_number}}" @endif></td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                            <tr>
                                <th><b>2.3 Number of students who appeared for the examinations conducted by the institution during the year:</b></th>
                                <th>
                                    <table style="width:100%">
                                        <tr>
                                            <th><b>Year</b></th>
                                            <td><input type="text" name="student_3_year" id="student_3_year" class="form-control" @if($student_3_year!='') value="{{$student_3_year}}" @endif></td>
                                        </tr>
                                        <tr>
                                            <th><b>Number</b></th>
                                            <td><input type="number" name="student_3_number" id="student_3_number" class="form-control" @if($student_3_number!='') value="{{$student_3_number}}" @endif></td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>

                            <tr>
                                <th colspan="2"><b>3. Academic:</b></th>
                            </tr>
                            <tr>
                                <th><b>3.1 Number of courses in all programmes during the year:</b></th>
                                <th>
                                    <table style="width:100%">
                                        <tr>
                                            <th><b>Year</b></th>
                                            <td><input type="text" name="academic_1_year" id="academic_1_year" class="form-control" @if($academic_1_year!='') value="{{$academic_1_year}}" @endif></td>
                                        </tr>
                                        <tr>
                                            <th><b>Number</b></th>
                                            <td><input type="number" name="academic_1_number" id="academic_1_number" class="form-control" @if($academic_1_number!='') value="{{$academic_1_number}}" @endif></td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                            <tr>
                                <th><b>3.2  Number of full-time teachers during the year:</b></th>
                                <th>
                                    <table style="width:100%">
                                        <tr>
                                            <th><b>Year</b></th>
                                            <td><input type="text" name="academic_2_year" id="academic_2_year" class="form-control" @if($academic_2_year!='') value="{{$academic_2_year}}" @endif></td>
                                        </tr>
                                        <tr>
                                            <th><b>Number</b></th>
                                            <td><input type="number" name="academic_2_number" id="academic_2_number" class="form-control" @if($academic_2_number!='') value="{{$academic_2_number}}" @endif></td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                            <tr>
                                <th><b>3.3  Number of sanctioned posts for the year:</b></th>
                                <th>
                                    <table style="width:100%">
                                        <tr>
                                            <th><b>Year</b></th>
                                            <td><input type="text" name="academic_3_year" id="academic_3_year" class="form-control" @if($academic_3_year!='') value="{{$academic_3_year}}" @endif></td>
                                        </tr>
                                        <tr>
                                            <th><b>Number</b></th>
                                            <td><input type="number" name="academic_3_number" id="academic_3_number" class="form-control" @if($academic_3_number!='') value="{{$academic_3_number}}" @endif></td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>

                            <tr>
                                <th colspan="2"><b>4. Institution:</b></th>
                            </tr>
                            <tr>
                                <th><b>4.1 Number of seats earmarked for reserved categories as per GOI/State Government during the year:</b></th>
                                <th>
                                    <table style="width:100%">
                                        <tr>
                                            <th><b>Year</b></th>
                                            <td><input type="text" name="institution_1_year" id="institution_1_year" class="form-control" @if($institution_1_year!='') value="{{$institution_1_year}}" @endif></td>
                                        </tr>
                                        <tr>
                                            <th><b>Number</b></th>
                                            <td><input type="number" name="institution_1_number" id="institution_1_number" class="form-control" @if($institution_1_number!='') value="{{$institution_1_number}}" @endif></td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                            <tr>
                                <th><b>4.2 Total number of classrooms and seminar halls: __________</b></th>
                                <th>
                                    <input type="number" name="institution_2_number" id="institution_2_number" class="form-control" @if($institution_2!='') value="{{$institution_2}}" @endif></td>
                                </th>
                            </tr>
                            <tr>
                                <th><b>4.3 Total number of computers on campus for academic purposes: __________</b></th>
                                <th>
                                    <input type="number" name="institution_3_number" id="institution_3_number" class="form-control" @if($institution_3!='') value="{{$institution_3}}" @endif></td>
                                </th>
                            </tr>

                             <tr>
                                <th><b>4.4 Total expenditure, excluding salary, during the year (INR in Lakhs): </b></th>
                                <th>
                                    <table style="width:100%">
                                        <tr>
                                            <th><b>Year</b></th>
                                            <td><input type="text" name="institution_4_year" id="institution_4_year" class="form-control"  @if($institution_4_year!='') value="{{$institution_4_year}}" @endif></td>
                                        </tr>
                                        <tr>
                                            <th><b>Number</b></th>
                                            <td><input type="number" name="institution_4_number" id="institution_4_number" class="form-control"  @if($institution_4_number!='') value="{{$institution_4_number}}" @endif></td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-12">
                    <center>
                    <a class="btn btn-primary" href='{{route("naac_parts.create")}}'>Back</a>
                    @if(empty($data['partA3']))
                        <input type="submit" value="Save" class="btn btn-success">
                    @else
                        <input type="submit" value="Update" class="btn btn-success">
                        <a class="btn btn-primary" href='{{route("naac_master.index")}}'>Next</a>
                    @endif
                    </center>
				</div>
                </form>
            </div>
    </div>
</div>
@include('includes.footerJs')
@include('includes.footer')
@endsection