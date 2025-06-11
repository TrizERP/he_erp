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
	/* color:#5c5cd8; */
	font-size:1.3rem;
	padding:20px;
}
</style>
<div id="page-wrapper">
	<div class="container-fluid">
		<div class="row bg-title">
			<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
				<h4 class="page-title">IQAC Part A1</h4>
			</div>
		</div>

	<div class="card">
			@if ($sessionData = Session::get('data')) @if($sessionData['status_code'] == 1)
		<div class="alert alert-success alert-block">
			@else
			<div class="alert alert-danger alert-block">
				@endif
				<button type="button" class="close" data-dismiss="alert">×</button>
				<strong>{{ $sessionData['message'] }}</strong>
			</div>
			@endif

			<form action="@if(!empty($data['partA1'])){{ route('naac_parts.update', $data['partA1']->id) }}@else {{route('naac_parts.store')}} @endif" enctype="multipart/form-data" method="post">
			<div class="row">
		
			@if(!empty($data['partA1']))
							{{ method_field("PUT") }}
							@else
							{{ method_field("POST") }}							
						@endif
				@csrf

				<div class="table-responsive">
					<table class="table table-bordered">
						<tr>
							<th colspan="2" class="text-center"><b>PART A</b></th>
						</tr>
						<tr>
							<th colspan="2"><b>Data of the Institution</b></th>
						</tr>
						<!-- part 1 institute data  -->
						<tr>
							<th class="headings"><b>1. Name of the Institution:</b></th>
							<th>
								<div class="institute_name form-group">
									<input type="text" name="institute_name" id="institute_name" class="form-control" placeholder="Enter Institute Name" value="{{$data['partA1']->institute_name ?? ''}}" autocomplete="off" required>
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Name of the head of the Institution:</b></th>
							<th>
								<div class="institute_head_name form-group">
									<input type="text" name="institute_head_name" id="institute_head_name" class="form-control"  placeholder="Enter Institute Head Name"  value="{{$data['partA1']->institute_head_name ?? ''}}" autocomplete="off" required>
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Designation :</b></th>
							<th>
								<div class="designation form-group">
									<input type="text" name="designation" id="designation" class="form-control" value="{{$data['partA1']->designation ?? ''}}"  placeholder="Enter Designation"  autocomplete="off" required>
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Does the Institution function from own campus</b></th>
							<th>
								<div class="institute_func_campus form-group">
									<select name="institute_func_campus" id="institute_func_campus" class="form-control">
									@foreach($data['yesOrNo'] as $val)
										<option value="{{ $val }}" {{ isset($data['partA1']->institute_func_campus) && $val == $data['partA1']->institute_func_campus ? 'selected' : '' }}>
											{{ $val }}
										</option>
									@endforeach
									</select>
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Phone No. of the Principal:</b></th>
							<th>
								<div class="princ_phno form-group">
									<input type="number" max="9999999999" name="princ_phno" id="princ_phno" class="form-control"  placeholder="Enter Phone No. of the Principal"  value="{{$data['partA1']->princ_phno ?? ''}}" autocomplete="off" required>
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Alternate Phone No. the Principal:</b></th>
							<th>
								<div class="princ_alternate_phno form-group">
									<input type="number" max="9999999999" name="princ_alternate_phno" id="princ_alternate_phno"  placeholder="Enter Alternate Phone No. the Principal" class="form-control" autocomplete="off" value="{{$data['partA1']->princ_alternate_phno ?? ''}}">
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Mobile No. (Principal):</b></th>
							<th>
								<div class="princ_mobile form-group">
									<input type="number" max="9999999999" name="princ_mobile" id="princ_mobile" class="form-control"  placeholder="Enter Mobile No. (Principal)" value="{{$data['partA1']->princ_mobile ?? ''}}" autocomplete="off" >
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Registered e-mail ID (Principal):</b></th>
							<th>
								<div class="princ_reg_email form-group">
									<input type="email"  name="princ_reg_email" id="princ_reg_email" class="form-control"  placeholder="Enter Registered e-mail ID (Principal)" value="{{$data['partA1']->princ_reg_email ?? ''}}" autocomplete="off" >
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Address:</b></th>
							<th>
								<div class="address form-group">
									<input type="text" name="address" id="address" title="max 500 word" class="form-control" placeholder="Enter Institute Address" value="{{$data['partA1']->address ?? ''}}" required autocomplete="off" >
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>City/Town:</b></th>
							<th>
								<div class="city_town form-group">
									<input type="text" name="city_town" id="city_town" placeholder="Enter City/Town"  class="form-control" value="{{$data['partA1']->city_town ?? ''}}" required autocomplete="off" >
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>State/UT:</b></th>
							<th>
								<div class="state_ut form-group">
									<input type="text" name="state_ut" id="state_ut" class="form-control" placeholder="Enter State/UT" value="{{$data['partA1']->state_ut ?? ''}}" required autocomplete="off" >
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Pin Code:</b></th>
							<th>
								<div class="pin_code form-group">
									<input type="number" max="9999999999" name="pin_code" id="pin_code" class="form-control" placeholder="Enter Pin Code" value="{{$data['partA1']->pin_code ?? ''}}" autocomplete="off" >
								</div>
							</th>							
						</tr>
						<!-- part 2 institute status  -->
						<tr>
							<th colspan="2" class="headings"><b>2. Institutional Status</b></th>
						</tr>
						<tr>
							<th><b>Autonomous Status (provide the date of conferment of Autonomy):</b></th>
							<th>
								<div class="confirm_autonomous_date form-group">
									<input type="text" name="confirm_autonomous_date" id="confirm_autonomous_date" class="form-control mydatepicker" @isset($data['partA1']->confirm_autonomous_date) value="{{$data['partA1']->confirm_autonomous_date }}" @endisset autocomplete="off" >
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Type of Institution:</b></th>
							<th>
								<div class="type_institute form-group">
									<select name="type_institute" id="type_institute" class="form-control">
									@foreach($data['InstituteType'] as $val)
										<option value="{{ $val }}" {{ isset($data['partA1']->type_institute) && $val == $data['partA1']->type_institute ? 'selected' : '' }}>
											{{ $val }}
										</option>
									@endforeach
									</select>
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Location:</b></th>
							<th>
								<div class="location form-group">
									<select name="location" id="location" class="form-control">
									@foreach($data['Location'] as $val)
										<option value="{{ $val }}" {{ isset($data['partA1']->location) && $val == $data['partA1']->location ? 'selected' : '' }}>
											{{ $val }}
										</option>
									@endforeach
									</select>
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Financial Status:</b></th>
							<th>
								<div class="financial_status form-group">
									<select name="financial_status" id="financial_status" class="form-control">
									@foreach($data['FinancialStatus'] as $val)
										<option value="{{ $val }}" {{ isset($data['partA1']->financial_status) && $val == $data['partA1']->financial_status ? 'selected' : '' }}>
											{{ $val }}
										</option>
									@endforeach
									</select>
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Name of the IQAC Co-ordinator/Director:</b></th>
							<th>
								<div class="IQAC_director_name form-group">
									<input type="text" name="IQAC_director_name" id="IQAC_director_name" class="form-control"  placeholder="Enter Name of the IQAC Co-ordinator/Director"  value="{{$data['partA1']->IQAC_director_name ?? ''}}" required autocomplete="off" >
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Phone No:</b></th>
							<th>
								<div class="phone_no form-group">
									<input type="number" max="9999999999" name="phone_no" id="phone_no" class="form-control" placeholder="Enter Phone Number" value="{{$data['partA1']->phone_no ?? ''}}" autocomplete="off" >
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Mobile No:</b></th>
							<th>
								<div class="mobile_no form-group">
									<input type="number" max="9999999999" name="mobile_no" id="mobile_no" class="form-control" placeholder="Enter Mobile Number" value="{{$data['partA1']->mobile_no ?? ''}}" autocomplete="off" >
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>IQAC e-mail ID:</b></th>
							<th>
								<div class="IQAC_email form-group">
									<input type="email" name="IQAC_email" id="IQAC_email" class="form-control" value="{{$data['partA1']->IQAC_email ?? ''}}" placeholder="Enter IQAC e-mail ID" autocomplete="off" >
								</div>
							</th>							
						</tr>
						<!-- part -3  -->
						<tr>
							<th colspan="2"  class="headings"><b>3. Website Address</b></th>
						</tr>
						<tr>
							<th><b>Web-link of the AQAR: (Previous Academic Year)</b></th>
							<th>
								<div class="web_add_link_AQAR form-group">
									<input type="text" name="web_add_link_AQAR" id="web_add_link_AQAR" class="form-control" placeholder="Enter Web-link of the AQAR: (Previous Academic Year)"  value="{{$data['partA1']->web_add_link_AQAR ?? ''}}" autocomplete="off" >
								</div>
							</th>	
						</tr>
						<!-- part -4  -->
						<tr>
							<th class="headings"><b>4. Whether Academic Calendar prepared during the year</b></th>
							<th>
								<div class="academic_calendar form-group">							
								<select name="academic_calendar" id="academic_calendar" class="form-control" onchange="handleSelectChange('academic_calendar','institute_weblink')">
									@foreach($data['yesOrNo'] as $val)
										<option value="{{ $val }}" {{ isset($data['partA1']->academic_calendar) && $val == $data['partA1']->academic_calendar ? 'selected' : '' }}>
											{{ $val }}
										</option>
									@endforeach
								</select>
								</div>
							</th>
						</tr>
						<tr>
							<th><b>if yes,whether it is uploaded in the institutional website:</b></th>
							<th>
								<div class="institute_weblink form-group">
									<input type="text" name="institute_weblink" id="institute_weblink" class="form-control" placeholder="Enter uploaded in the institutional website" value="{{$data['partA1']->institute_weblink ?? ''}}" autocomplete="off" >
								</div>
							</th>	
						</tr>
						<!-- part -5  -->
						<tr>
							<th colspan="2"  class="headings"><b>5. Accrediation Details</b></th>
						</tr>
						<tr>
							<th colspan="2">
								<div class="accrediation_details form-group">							
									<textarea class="summernote" id="accrediation_details" name="accrediation_details">
									@if(isset($data['partA1']->accrediation_details)){!! $data['partA1']->accrediation_details !!}
								 	@endif
									</textarea>
								</div>										
							</th>	
						</tr>
						<!-- part -6  -->
						<tr>
							<th class="headings"><b>6. Date of Establishment of IQAC:</b></th>
							<th>
							<div class="IQAC_establish_date form-group">
								<input type="text" name="IQAC_establish_date" id="IQAC_establish_date" class="form-control mydatepicker" @isset($data['partA1']->IQAC_establish_date) value="{{$data['partA1']->IQAC_establish_date}}" @endisset autocomplete="off" >
								</div>
							</th>
						</tr>
						<!-- part -7  -->
						<tr>
							<th colspan="2"  class="headings"><b>7. Internal Quality Assurance System</b></th>
						</tr>
						<tr>
							<th colspan="2">
								<div class="institute_assurance form-group">
									<textarea class="summernote" id="institute_assurance" name="institute_assurance">
									@if(isset($data['partA1']->institute_assurance)){!! $data['partA1']->institute_assurance !!}
									@endif
									</textarea>
									<input type="file" name="assurance_file" id="assurance_file" autocomplete="off"> 
									@if(isset($data['partA1']->assurance_file)) <img src="{{asset('/Images/square-check.svg')}}" alt=""> <a target="_blank" href="https://s3-triz.fra1.digitaloceanspaces.com/public/naac/{{$data['partA1']->assurance_file}}">View File</a> 
									<input type="hidden" name="assurance_file_name" id="assurance_file_name" value="{{$data['partA1']->assurance_file}}">
									 @endif
								</div>								
							</th>	
						</tr>
						<!-- part -8  -->
						<tr>
							<th colspan="2"  class="headings"><b>8. Provide the list of Special Status conferred by Central/ State Government- UGC/CSIR/DST/DBT/ICMR/TEQIP/World Bank/CPE of UGC etc.</b></th>
						</tr>
						<tr>
							<th colspan="2">
								<div class="special_conferred_status form-group">
									<textarea class="summernote" id="special_conferred_status" name="special_conferred_status">
									@if(isset($data['partA1']->special_conferred_status)){!! $data['partA1']->special_conferred_status !!}
									@endif
									
									</textarea>
									<input type="file" name="conferred_status_file" id="conferred_status_file" autocomplete="off"> @if(isset($data['partA1']->conferred_status_file)) <img src="{{asset('/Images/square-check.svg')}}" alt=""><a target="_blank" href="https://s3-triz.fra1.digitaloceanspaces.com/public/naac/{{$data['partA1']->conferred_status_file}}">View File</a> 
									<input type="hidden" name="conferred_file_name" id="conferred_file_name" value="{{$data['partA1']->conferred_status_file}}">
									@endif
								</div>								
							</th>	
						</tr>
						<!-- part -9  -->
							<tr>
							<th class="headings"><b>9. Whether composition of IQAC as per latest NAAC guidelines:</b></th>
							<th>
							<div class="IQAC_composition form-group">							
								<select name="IQAC_composition" id="IQAC_composition" class="form-control"  onchange="handleSelectChange('IQAC_composition','composition_file')">
									@foreach($data['yesOrNo'] as $val)
										<option value="{{ $val }}" {{ isset($data['partA1']->IQAC_composition) && $val == $data['partA1']->IQAC_composition ? 'selected' : '' }}>
											{{ $val }}
										</option>
									@endforeach
								</select>
							</div>		
							</th>					
						</tr>
						<tr>
							<th><b>Upload latest notification of formation of IQAC</b></th>
							<th>
								<div class="composition_file form-group">
								<input type="file" name="composition_file" id="composition_file" class="form-control" autocomplete="off" >@if(isset($data['partA1']->composition_file)) <img src="{{asset('/Images/square-check.svg')}}" alt=""> <a target="_blank" href="https://s3-triz.fra1.digitaloceanspaces.com/public/naac/{{$data['partA1']->composition_file}}">View File</a>
								<input type="hidden" name="composition_file_name" id="composition_file_name" value="{{$data['partA1']->composition_file}}">
								@endif
								</div>
							</th>	
						</tr>
						<!-- part -10  -->
						<tr>
							<th class="headings"><b>10. Number of IQAC meetings held during the year :</b></th>
							<th>
								<div class="no_IQAC_meeting form-group">
								<input type="number" name="no_IQAC_meeting" id="no_IQAC_meeting" class="form-control" placeholder="Enter Number of IQAC meetings" value="{{$data['partA1']->no_IQAC_meeting ?? ''}}" autocomplete="off" >
								</div>
							</th>
						</tr>
						<tr>
							<th><b>The minutes of IQAC meeting and compliances to the decisions have been uploaded on the institutional website</b></th>
							<th>
							<div class="minutes_IQAC_meeting form-group">
								<select name="minutes_IQAC_meeting" id="minutes_IQAC_meeting" class="form-control" onchange="handleSelectChange('minutes_IQAC_meeting','uploaded_minutes')">
									@foreach($data['yesOrNo'] as $val)
										<option value="{{ $val }}" {{ isset($data['partA1']->minutes_IQAC_meeting) && $val == $data['partA1']->minutes_IQAC_meeting ? 'selected' : '' }}>
											{{ $val }}
										</option>
									@endforeach
								</select>
							</div>
						</th>							
						</tr>
						<tr>
							<th><b>Upload the minutes of meeting and action taken report</b></th>
							<th>
								<div class="uploaded_minutes form-group">
								<input type="file" name="uploaded_minutes" id="uploaded_minutes" class="form-control" autocomplete="off" >@if(isset($data['partA1']->uploaded_minutes)) <img src="{{asset('/Images/square-check.svg')}}" alt=""> <a target="_blank" href="https://s3-triz.fra1.digitaloceanspaces.com/public/naac/{{$data['partA1']->uploaded_minutes}}">View File</a>
								<input type="hidden" name="uploaded_minutes_name" id="uploaded_minutes_name" value="{{$data['partA1']->uploaded_minutes}}">
								@endif
								</div>
							</th>	
						</tr>
						<!-- part -11 -->
						<tr>
							<th class="headings"><b>11. Whether IQAC received funding from any of the funding agency to support its activities during the year?</b></th>
							<th>
								<div class="IQAC_recive_fund form-group">
									<select name="IQAC_recive_fund" id="IQAC_recive_fund" class="form-control">
									@foreach($data['yesOrNo'] as $val)
										<option value="{{ $val }}" {{ isset($data['partA1']->IQAC_recive_fund) && $val == $data['partA1']->IQAC_recive_fund ? 'selected' : '' }}>
											{{ $val }}
										</option>
									@endforeach
									</select>
								</div>
							</th>
						</tr>
						<tr>
							<th><b>If yes, mention the amount</b></th>
							<th>
								<div class="fund_amt form-group">							
									<input type="text" name="fund_amt" id="fund_amt" class="form-control" value="{{$data['partA1']->fund_amt ?? ''}}" placeholder="Enter Fund amount" autocomplete="off" >
								</div>
							</th>
						</tr>
						<tr>
							<th><b>Year</b></th>
							<th>
								<div class="fund_year form-group">							
									<input type="text" name="fund_year" id="fund_year" class="form-control" value="{{$data['partA1']->fund_year ?? ''}}"  placeholder="Enter Fund year" autocomplete="off" >
								</div>
							</th>	
						</tr>
						<!-- part -12  -->
						<tr>
							<th colspan="2"  class="headings"><b>12. Significant contributions made by IQAC during the current year(maximum five bullets)</b></th>
						</tr>
						<tr>
							<th colspan="2">
								<textarea class="summernote" id="IQAC_significant_contribution" name="IQAC_significant_contribution">
								@if(isset($data['partA1']->IQAC_significant_contribution)){!! $data['partA1']->IQAC_significant_contribution !!}
								 @endif
								</textarea>
								<div class="contribution_file form-group">
								<input type="file" name="contribution_file" id="contribution_file" class="form-control" autocomplete="off" >@if(isset($data['partA1']->contribution_file)) <img src="{{asset('/Images/square-check.svg')}}" alt=""> <a target="_blank" href="https://s3-triz.fra1.digitaloceanspaces.com/public/naac/{{$data['partA1']->contribution_file}}">View File</a>
								<input type="hidden" name="contribution_file_name" id="contribution_file_name" value="{{$data['partA1']->contribution_file}}">@endif
								</div>
							</th>	
						</tr>
						<!-- part -13  -->
						<tr>
							<th colspan="2"  class="headings"><b>13. Plan of action chalked out by the IQAC in the beginning of the academic year towards Quality Enhancement and outcome achieved by the end of the academic year</b></th>
						</tr>
						<tr>
							<th colspan="2">
								<textarea class="summernote" id="action_chalked_out" name="action_chalked_out">
								@if(isset($data['partA1']->action_chalked_out)){!! $data['partA1']->action_chalked_out !!}
								 @endif
								</textarea>
								<div class="action_chalked_out_file form-group">
								<input type="file" name="action_chalked_out_file" id="action_chalked_out_file" class="form-control" autocomplete="off" >@if(isset($data['partA1']->action_chalked_out_file)) <img src="{{asset('/Images/square-check.svg')}}" alt=""> <a target="_blank" href="https://s3-triz.fra1.digitaloceanspaces.com/public/naac/{{$data['partA1']->action_chalked_out_file}}">View File</a>
								<input type="hidden" name="action_file_name" id="action_file_name" value="{{$data['partA1']->action_chalked_out_file}}">@endif
								</div>
							</th>	
						</tr>
						<!-- part -14  -->
						<tr>
							<th class="headings"><b>14. Whether AQAR was placed before statutory body ?</b></th>
							<th>
							<div class="AQAR_placed_statutory form-group">							
								<select name="AQAR_placed_statutory" id="AQAR_placed_statutory" class="form-control">
									@foreach($data['yesOrNo'] as $val)
										<option value="{{ $val }}" {{ isset($data['partA1']->AQAR_placed_statutory) && $val == $data['partA1']->AQAR_placed_statutory ? 'selected' : '' }}>
											{{ $val }}
										</option>
									@endforeach
								</select>
							</div>
						</th>							
						</tr>
						<tr>
							<th><b>Statutory Name:</b></th>
							<th>
								<div class="statutory_name form-group">
								<input type="text" name="statutory_name" id="statutory_name" placeholder="Enter Statutory Name" class="form-control" value="{{$data['partA1']->statutory_name ?? ''}}" autocomplete="off" >
								</div>
							</th>	
						</tr>
						<tr>
							<th><b>Statutory Date:</b></th>
							<th>
								<div class="statutory_date form-group">
								<input type="text" name="statutory_date" id="statutory_date" class="form-control mydatepicker" @isset($data['partA1']->statutory_date) value="{{$data['partA1']->statutory_date}}" @endisset autocomplete="off" >
								</div>
							</th>	
						</tr>
							<!-- part -15  -->
							<tr>
							<th class="headings"><b>15. Whether NAAC/or any other accredited body(s) visited IQAC or interacted with it to assess the functioning ?</b></th>
							<th>
							<div class="NAAC_or_other form-group">							
								<select name="NAAC_or_other" id="NAAC_or_other" class="form-control">
									@foreach($data['yesOrNo'] as $val)
										<option value="{{ $val }}" {{ isset($data['partA1']->NAAC_or_other) && $val == $data['partA1']->NAAC_or_other ? 'selected' : '' }}>
											{{ $val }}
										</option>
									@endforeach
								</select>
							</div>
						</th>							
						</tr>
						<!-- part -16  -->
						<tr>
							<th class="headings"><b>16. Whether institutional data submitted to AISHE:</b></th>
							<th>
								<div class="submitted_AISHE form-group">
									<select name="submitted_AISHE" id="submitted_AISHE" class="form-control">
										@foreach($data['yesOrNo'] as $val)
											<option value="{{ $val }}" {{ isset($data['partA1']->submitted_AISHE) && $val == $data['partA1']->submitted_AISHE ? 'selected' : '' }}>
												{{ $val }}
											</option>
										@endforeach
									</select>
								</div>
							</th>							
						</tr>
						<tr>
							<th><b>Year of Submission</b></th>
							<th>
								<div class="year_submission form-group">
								<input type="text" name="year_submission" id="year_submission" class="form-control" placeholder="Enter Year" value="{{ $data['partA1']->year_submission ?? '' }}" autocomplete="off">
								</div>
							</th>	
						</tr>
						<tr>
							<th><b>Date of Submission:</b></th>
							<th>
								<div class="date_submission form-group">
								<input type="text" name="date_submission" id="date_submission" class="form-control mydatepicker" @isset($data['partA1']->date_submission) value="{{$data['partA1']->date_submission}}" @endisset autocomplete="off" >
								</div>
							</th>	
						</tr>
						<!-- part -17  -->
						<tr>
							<th class="headings"><b>17. Does the Institution have Management Information System ?</b></th>
							<th>
								<div class="management_info form-group">							
								<select name="management_info" id="management_info" class="form-control" onchange="handleSelectChange('management_info','brief_desc')">
									@foreach($data['yesOrNo'] as $val)
										<option value="{{ $val }}" {{ isset($data['partA1']->management_info) && $val == $data['partA1']->management_info ? 'selected' : '' }}>
											{{ $val }}
										</option>
									@endforeach
								</select>
								</div>
							</th>
						</tr>
						<tr>
							<th><b>If yes, give a brief descripiton and a list of modules currently operational (maximum 500 words)</b></th>
							<th>
								<textarea name="brief_desc" id="brief_desc" cols="30" rows="5" class="form-control">{{$data['partA1']->brief_desc ?? ''}}</textarea>
							</th>
						</tr>
					</table>
				</div>
				<!-- submit button to store -->
				<div class="col-md-12">
				<center>
				@if(empty($data['partA1']))
					<input type="submit" value="Save" class="btn btn-success">
				@else
					<input type="submit" value="Update" class="btn btn-success">
					<a class="btn btn-primary" href='{{route("naac_parts.create")}}'>Next</a>
				@endif
				</center>
				</div>			
		</form>
	</div>


	</div>
</div>

@include('includes.footerJs')
	<script src="{{asset('/plugins/bower_components/summernote/dist/summernote.min.js')}}"></script>
<script>
$( document ).ready(function() { 

    $('.summernote').summernote({
        height: 200, // set editor height
        minHeight: null, // set minimum height of editor
        maxHeight: null, // set maximum height of editor
        focus: false // set focus to editable area after initializing summernote
    });

    $('[data-toggle="popover"]').popover({title: "",html: true});
    
    $('[data-toggle="popover"]').on('click', function (e) {
        $('[data-toggle="popover"]').not(this).popover('hide');
    });

});
 	function add_rows(tableId) {
	//  alert(tableId);
		 var table = document.getElementById(tableId);
		 
		var newRow = table.insertRow(table.rows.length);
		var numberOfColumns = table.rows.length > 0 ? table.rows[0].cells.length : 0;
        // alert(numberOfColumns);
		
		for (var i = 0; i < numberOfColumns; i++) {
			var cell = newRow.insertCell(i);
			cell.innerHTML = ''; // You can customize the content of each cell here
		}
    }
	function add_columns(tableId) {
        var table = document.getElementById(tableId);
        var rowCount = table.rows.length;
        for (var i = 0; i < rowCount; i++) {
            var cell = table.rows[i].insertCell(-1);
            cell.innerHTML = '';
        }
    }

	  function handleSelectChange(selectId,inputId) {
		
        var selectedValue = document.getElementById(selectId).value;
        var fileInput = document.getElementById(inputId);

        if (selectedValue === "Yes") {
            fileInput.setAttribute("required", "required");
        } else {
            fileInput.removeAttribute("required");
        }
    }
</script>

@include('includes.footer')
@endsection