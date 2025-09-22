@include('includes.headcss')
<link rel="stylesheet" href="../../../plugins/bower_components/dropify/dist/css/dropify.min.css">
@include('includes.header')
@include('includes.sideNavigation')
<style type="text/css">
    br {
        display: block;
    }
    #pastEducation td,#pastEducation th{
        padding:0.4rem !important;
    }
    .addButtonCheckboxProfessional{
        border : 1px solid #ddd;
        border-radius : 20px;
        margin : 6px 0px;
        padding :10px;
    }
</style>

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Edit User</h4>
            </div>
        </div>
        <div class="card">
            <!-- @TODO: Create a saperate tmplate for messages and include in all tempate -->
            @if ($message = Session::has('success'))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ Session::get('success') }}</strong>
                </div>
            @endif
            @if ($message = Session::has('error'))
                <div class="alert alert-danger alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ Session::get('error') }}</strong>
                </div>
            @endif
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <div class="sttabs tabs-style-linemove triz-verTab bg-white style2">
                        <center>
                            <ul class="nav nav-tabs tab-title mb-4">
                                <li class="nav-item"><a href="#section-linemove-1" class="nav-link active" aria-selected="true" data-toggle="tab"><span>Personal Details</span></a>
                                </li>
                                <li class="nav-item"><a href="#section-linemove-2" class="nav-link" aria-selected="false" data-toggle="tab"><span>Past Education</span></a>
                                </li>
                                <li class="nav-item"><a href="#section-linemove-3" class="nav-link" aria-selected="false" data-toggle="tab"><span>Experience Details</span></a></li>
                                <li class="nav-item"><a href="#section-linemove-4" class="nav-link" aria-selected="false" data-toggle="tab"><span>Training Details</span></a></li>
                                <li class="nav-item"><a href="#section-linemove-5" class="nav-link" aria-selected="false" data-toggle="tab"><span>Professional Details</span></a>
                                </li>
                                <li class="nav-item"><a href="#section-linemove-6" class="nav-link" aria-selected="false" data-toggle="tab"><span>Salary Details</span></a></li>
                                <li class="nav-item"><a href="#section-linemove-7" class="nav-link" aria-selected="false" data-toggle="tab"><span> Staff Document</span></a>
                                </li>
                                <li class="nav-item"><a href="#section-linemove-8" class="nav-link" aria-selected="false" data-toggle="tab"><span>My Skills & Certifications</span></a></li>

                            </ul>
                        </center>
                    </div>

                    @php
                        $departments = $data['departments'];
                        $new_emp_code = $data['new_emp_code'];
                        $employees = $data['employees'];
                        $job_titles = $data['job_titles'];
                        $user_profiles = $data['user_profiles'];
                        $subject_data = $data['subject_data'];
                        $subject_data_selected_arr = $data['subject_data_selected_arr'];
                        $custom_fields = $data['custom_fields'];
                        $data_fields = $data['data_fields'];
                        $past_educations = $data['past_educations'];
                        $experience_details = $data['experience_details'];
                        $training_details = $data['training_details'];
                        $sub_std_map = $data['sub_std_map'];
                        $professional_details = $data['professional_details'];
                        $salary_details = $data['salary_details'];
                        $document_details = $data['document_details'];
                        $categorties = $data['categorties'];
                        $religions = $data['religions'];
                        $bloodgroups = $data['bloodgroups'];
                        $maretial_status = $data['maretial_status'];
                        $data = $data['data'];
                    @endphp
                    <div class="tab-content">
                        <div class="tab-pane p-3 active" id="section-linemove-1" role="tabpanel">
                            <form action="{{ route('add_user.update', $data['id']) }}" enctype="multipart/form-data"
                                  method="post">
                                {{ method_field("PUT") }}
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label>Name Suffix</label>
                                        <select name="name_suffix" id="name_suffix" class="form-control" required>
                                            <option> Select Name Suffix</option>
                                            <option value="Mr."
                                                    @if(isset($data))@if("Mr." == $data['name_suffix']) selected @endif  @endif>
                                                Mr.
                                            </option>
                                            <option value="Mrs."
                                                    @if(isset($data))@if("Mrs." == $data['name_suffix']) selected @endif  @endif>
                                                Mrs.
                                            </option>
                                            <option value="Miss."
                                                    @if(isset($data))@if("Miss." == $data['name_suffix']) selected @endif  @endif>
                                                Miss.
                                            </option>
                                            <option value="Dr."
                                                    @if(isset($data))@if("Dr." == $data['name_suffix']) selected @endif  @endif>
                                                Dr.
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>First Name </label>
                                        <input type="text" id='first_name'
                                               value="@if(isset($data['first_name'])){{ $data['first_name'] }}@endif"
                                               required name="first_name" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Middle Name</label>
                                        <input type="text"
                                               value="@if(isset($data['middle_name'])){{ $data['middle_name'] }}@endif"
                                               id='middle_name' required name="middle_name" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Last Name</label>
                                        <input type="text" onchange="getUsername();"
                                               value="@if(isset($data['last_name'])){{ $data['last_name'] }}@endif"
                                               id='last_name' required name="last_name" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>User Name </label>
                                        <input type="text"
                                               value="@if(isset($data['user_name'])){{ $data['user_name'] }}@endif"
                                               id='user_name' required name="user_name" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Email</label>
                                        <!--<span><br><b>{{ $data['email'] }}</b></span>-->
                                        <input type="text" id='email'
                                               value="@if(isset($data['email'])){{ $data['email'] }}@endif" required
                                               name="email" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Mobile</label>
                                        <input type="text" value="@if(isset($data['mobile'])){{ $data['mobile'] }}@endif" id='mobile' required name="mobile" class="form-control">
                                    </div>
                                    
                                    <div class="col-md-4 form-group">
                                        <label>Subject</label>
                                        <select name="subject_ids[]" id="subject_ids[]" class="form-control" multiple
                                                style="height:200px;">
                                            <option value="0"> Select Subject</option>
                                            @if(!empty($subject_data))
                                                @foreach($subject_data as $key => $val)
                                                    <option value="{{ $val['id'] }}"

                                                            @php if( in_array($val['id'],$subject_data_selected_arr) ){ echo "selected"; }@endphp

                                                    > {{ $val['subject_name'] }} </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="control-label">Gender</label>
                                        <div class="radio-list">
                                            <label class="radio-inline p-0">
                                                <div class="radio radio-success">
                                                    <input type="radio"
                                                           @if(isset($data))@if("M" == $data['gender']) checked
                                                           @endif  @endif name="gender" id="male" value="M" required>
                                                    <label for="male">Male</label>
                                                </div>
                                            </label>
                                            <label class="radio-inline">
                                                <div class="radio radio-success">
                                                    <input type="radio"
                                                           @if(isset($data))@if("F" == $data['gender']) checked
                                                           @endif  @endif name="gender" id="female" value="F" required>
                                                    <label for="female">Female</label>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- 23-10-2024 -->
                                    <div class="col-md-4 form-group">
                                        <label>Aadhar Card No.</label>
                                        <input type="text" value="@if(isset($data['aadhar_card'])){{ $data['aadhar_card'] }}@endif" id='aadhar_card' required name="aadhar_card" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>PAN Card No.</label>
                                        <input type="text" value="@if(isset($data['pan_card'])){{ $data['pan_card'] }}@endif" id='pan_card' required name="pan_card" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Category</label>
                                        <select id='category' name="category" class="form-control">
                                            <option value="0">Select Category</option>
                                            @foreach($categorties as $title)
                                                <option value="{{$title->id}}" @if($data['category'] == $title->id) selected @endif>{{$title->title}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Religion</label>
                                        <select id='religion' name="religion" class="form-control">
                                            <option value="0">Select Religion</option>
                                            @foreach($religions as $title)
                                                <option value="{{$title->id}}" @if($data['religion'] == $title->id) selected @endif>{{$title->religion_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Bloog Group</label>
                                        <select id='bloodgroup' name="bloodgroup" class="form-control">
                                            <option value="0">Select blood type</option>
                                            @foreach($bloodgroups as $title)
                                                <option value="{{$title->id}}" @if($data['bloodgroup'] == $title->id) selected @endif>{{$title->bloodgroup }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Marital Status</label>
                                        <select id='marital_status' name="marital_status" class="form-control">
                                            <option value="0">Select Marital Status</option>
                                            @foreach($maretial_status as $title)
                                                <option value="{{$title}}" @if($data['marital_status'] == $title) selected @endif>{{$title}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- 23-10-2024 end  -->
                                    <div class="col-md-4 form-group">
                                        <label>Address</label>
                                        <textarea class="form-control" required name="address">@if(isset($data['address']))
                                                {{ $data['address'] }}
                                            @endif</textarea>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>City</label>
                                        <input type="text" value="@if(isset($data['city'])){{ $data['city'] }}@endif"
                                               id='city' required name="city" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>State</label>
                                        <input type="text" value="@if(isset($data['state'])){{ $data['state'] }}@endif"
                                               id='state' required name="state" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Pincode</label>
                                        <input type="number"
                                               value="@if(isset($data['pincode'])){{$data['pincode']}}@endif"
                                               id='pincode' required name="pincode" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>User Profile</label>
                                        <select name="user_profile_id" required id="user_profile_id"
                                                class="form-control">
                                            <option value="0"> Select Parent Profile</option>

                                            @if(!empty($user_profiles))
                                                @foreach($user_profiles as $key => $value)

                                                    <option value="{{ $value['id'] }}"
                                                            @if(isset($data)) @if($value['id'] == $data['user_profile_id']) selected @endif  @endif > {{ $value['name'] }} </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group" id="total_lecture_div">
                                        <label>Total Lectures</label>
                                        <input type="number" id='total_lecture' name="total_lecture"
                                               class="form-control"
                                               value="@if(isset($data['total_lecture'])){{$data['total_lecture']}}@endif">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Join Year</label>
                                        <input type="number"
                                               value="@if(isset($data['join_year'])){{$data['join_year']}}@endif"
                                               id='join_year' required name="join_year" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Password</label>
                                        <input type="password"
                                               value="@if(isset($data['password'])){{ $data['password'] }}@endif"
                                               id='password' required name="password" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Birthdate</label>
                                        <div class="input-daterange input-group" id="date-range">
                                            <input type="text" required class="form-control mydatepicker"
                                                   placeholder="dd/mm/yyyy"
                                                   value="@if(isset($data['birthdate'])){{$data['birthdate']}}@endif"
                                                   name="birthdate" autocomplete="off"><span
                                                    class="input-group-addon"><i class="icon-calender"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group ml-0 mr-0">
                                        <label>Inactive Status</label>
                                        <select id='status' name="status" class="form-control">
                                            <option value="1"
                                                    @if(isset($data['status'])) @if($data['status'] == 1) selected @endif @endif>
                                                No
                                            </option>
                                            <option value="0"
                                                    @if(isset($data['status'])) @if($data['status'] == 0) selected @endif @endif>
                                                Yes
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-sm-4 ol-md-4 col-xs-12">
                                        <label for="input-file-now">User Image</label>
                                        <input type="file" accept="image/*" name="user_image"
                                               @if(isset($data))data-default-file="/storage/user/{{ $data['image'] }}"
                                               @else required @endif id="input-file-now" class="dropify"/>
                                    </div>
                                    @if(isset($custom_fields))
                                        @foreach($custom_fields as $key => $value)
                                            <div class="col-md-4 form-group">
                                                <label>{{ $value['field_label'] }}</label>
                                                @if($value['field_type'] == 'file')
                                                    <input type="{{ $value['field_type'] }}" accept="image/*"
                                                           id="input-file-now"
                                                           data-default-file="@if(isset($data[$value['field_name']])){{'/storage/user/'.$data[$value['field_name']]}}@endif"
                                                           required name="{{ $value['field_name'] }}"
                                                           class="form-control">
                                                @elseif($value['field_type'] == 'date')
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control mydatepicker"
                                                               placeholder="dd/mm/yyyy"
                                                               value="@if(isset($data[$value['field_name']])){{$data[$value['field_name']]}}@endif"
                                                               autocomplete="off" id="{{ $value['field_name'] }}"
                                                               required name="{{ $value['field_name'] }}"
                                                               class="form-control"><span class="input-group-addon"><i
                                                                    class="icon-calender"></i></span>
                                                    </div>
                                                @elseif($value['field_type'] == 'checkbox')
                                                    <div class="checkbox-list">
                                                        @if(isset($data_fields[$value['id']]))
                                                            @foreach($data_fields[$value['id']] as $keyData => $valueData )
                                                                <label class="checkbox-inline">
                                                                    <div class="checkbox checkbox-success">
                                                                        <input type="checkbox"
                                                                               @if($valueData['display_value'] == $data[$value['field_name']]) checked
                                                                               @endif name="{{ $value['field_name'] }}[]"
                                                                               value="{{ $valueData['display_value'] }}"
                                                                               id="{{ $valueData['display_value'] }}"
                                                                               required>
                                                                        <label for="{{ $valueData['display_value'] }}">{{ $valueData['display_text'] }}</label>
                                                                    </div>
                                                                </label>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                @elseif($value['field_type'] == 'dropdown')
                                                    <select name="{{ $value['field_name'] }}" class="form-control"
                                                            required id="{{ $value['field_name'] }}">
                                                        <option value="">
                                                            SELECT {{ strtoupper($value['field_label']) }} </option>

                                                        @if(isset($data_fields[$value['id']]))
                                                            @foreach($data_fields[$value['id']] as $keyData => $valueData)
                                                                @php
                                                                    $selected = '';
                                                                @endphp
                                                                @if(isset($data))
                                                                    @if($data[$value['field_name']]== $valueData['display_value'])
                                                                        @php
                                                                            $selected = 'selected';
                                                                        @endphp
                                                                    @endif
                                                                @endif
                                                                <option value="{{ $valueData['display_value'] }}" {{$selected}}> {{ $valueData['display_text'] }} </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                @elseif($value['field_type'] == 'textarea')
                                                    <textarea id="{{ $value['field_name'] }}" class="form-control"
                                                              required name="{{ $value['field_name'] }}"
                                                              placeholder="{{ $value['field_message'] }}">
                                @if(isset($data[$value['field_name']]))
                                                            {{$data[$value['field_name']]}}
                                                        @endif
                                </textarea>
                                                @else
                                                    <input type="{{ $value['field_type'] }}"
                                                           id="{{ $value['field_name'] }}"
                                                           value="@if(isset($data[$value['field_name']])){{$data[$value['field_name']]}}@endif"
                                                           placeholder="{{ $value['field_message'] }}" required
                                                           name="{{ $value['field_name'] }}" class="form-control">
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                    
                                    <div class="col-md-4 form-group">
                                        <label>Employee Id</label>
                                        <input type="text" id='employee_no' name="employee_no"
                                                    class="form-control" value="{{ $new_emp_code }}">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Job Title</label>
                                        <select id='jobtitle_id' name="jobtitle_id" class="form-control">
                                            <option value="0">Select Title</option>
                                            @foreach($job_titles as $title)
                                                @if(isset($data['jobtitle_id']) && $data['jobtitle_id'] == $title->id)
                                                    <option selected value="{{$title->id}}">{{$title->title}}</option>
                                                @else
                                                    <option value="{{$title->id}}">{{$title->title}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Branch</label>
                                        <select id='department_id' name="department_id" class="form-control">
                                            <option value="0">Select Branch</option>
                                            @foreach($departments as $title)
                                                <option value="{{$title->id}}"
                                                        @if($data['department_id'] == $title->id) selected @endif>{{$title->department}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Joining Date</label>
                                        <input type="date" id='joined_date' name="joined_date"
                                               value="{{ $data['joined_date'] ? date('Y-m-d',strtotime($data['joined_date'])) : '' }}"
                                               class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Probation Period From</label>
                                        <input type="date" id='probation_period_from' name="probation_period_from"
                                               value="{{ $data['probation_period_from'] ? date('Y-m-d',strtotime($data['probation_period_from'])) : '' }}"
                                               class="form-control">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Probation Period To</label>
                                        <input type="date" id='probation_period_to'
                                               value="{{ $data['probation_period_to'] ? date('Y-m-d',strtotime($data['probation_period_to'])) : '' }}"
                                               name="probation_period_to" class="form-control">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Terminated Date</label>
                                        <input type="date" id='terminated_date'
                                               value="{{ $data['terminated_date'] ? date('Y-m-d',strtotime($data['terminated_date'])) : '' }}"
                                               name="terminated_date" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Termination Reason</label>
                                        <input type="text" id='termination_reason'
                                               value="{{$data['termination_reason']}}" name="termination_reason"
                                               class="form-control">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Notice From Date</label>
                                        <input type="date" id='notice_fromdate'
                                               value="{{ $data['notice_fromdate'] ? date('Y-m-d',strtotime($data['notice_fromdate'])) : '' }}"
                                               name="notice_fromdate" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Notice To Date</label>
                                        <input type="date" id='notice_todate'
                                               value="{{ $data['notice_todate'] ? date('Y-m-d',strtotime($data['notice_todate'])) : '' }}"
                                               name="notice_todate" class="form-control">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Notice Reason</label>
                                        <input type="text" id='noticereason' value="{{$data['noticereason']}}"
                                               name="noticereason" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Opening Leave</label>
                                        <input type="text" id='openingleave' value="{{$data['openingleave']}}"
                                               name="openingleave" class="form-control">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Relieving Date</label>
                                        <input type="date" id='relieving_date'
                                               value="{{ $data['relieving_date'] ? date('Y-m-d',strtotime($data['relieving_date'])) : '' }}"
                                               name="relieving_date" class="form-control">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Relieving Reason</label>
                                        <input type="text" id='relieving_reason' value="{{$data['relieving_reason']}}"
                                               name="relieving_reason" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>CL Opening Leave</label>
                                        <input type="text" id='CL_opening_leave' value="{{$data['CL_opening_leave']}}"
                                               name="CL_opening_leave" class="form-control">
                                    </div>

                                    <div class="col-md-12 form-group">
                                        <h4>Report To</h4>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Supervisor / Subordinate</label>
                                        <select id='supervisor_opt' name="supervisor_opt" class="form-control">

                                            @if(isset($data['supervisor_opt']) && $data['supervisor_opt'] == "Supervisor")
                                                <option value="Supervisor">Supervisor</option>
                                                <option value="Subordinate" selected>Subordinate</option>
                                            @else
                                                <option value="Supervisor">Supervisor</option>
                                                <option value="Subordinate" selected>Subordinate</option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Employee Name</label>
                                        <select id='employee_id' name="employee_id" class="form-control">
                                            <option value="0">Select Employee</option>
                                            @foreach($employees as $title)
                                                @if(isset($data['employee_id']) && $data['employee_id'] == $title->id)
                                                    <option selected
                                                            value="{{$title->id}}">{{$title->first_name .' ' . $title->last_name}}</option>
                                                @else
                                                    <option value="{{$title->id}}">{{$title->first_name .' ' . $title->last_name}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Reporting Method</label>
                                        <select id='reporting_method' name="reporting_method" class="form-control">
                                            @if(isset($data['reporting_method']) && $data['reporting_method'] == "Direct")
                                                <option value="Direct" selected>Direct</option>
                                                <option value="Indirect">In Direct</option>
                                            @else
                                                <option value="Direct">Direct</option>
                                                <option value="Indirect" selected>In Direct</option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-12 form-group">
                                        <h4>Direct Deposit</h4>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Bank Name</label>
                                        <input type="text" id='bank_name' value="{{$data['bank_name']}}"
                                               name="bank_name" class="form-control">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Branch Name</label>
                                        <input type="text" id='branch_name' value="{{$data['branch_name']}}"
                                               name="branch_name" class="form-control">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Account</label>
                                        <input type="text" id='account_no' value="{{$data['account_no']}}"
                                               name="account_no" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>IFSC</label>
                                        <input type="text" id='ifsc_code' value="{{$data['ifsc_code']}}"
                                               name="ifsc_code" class="form-control">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Amount</label>
                                        <input type="text" id='amount' value="{{$data['amount']}}" name="amount"
                                               class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Transfer Type</label>
                                        <select id='transfer_type' name="transfer_type" class="form-control">
                                            @if(isset($data['transfer_type']) && $data['transfer_type'] == "Direct")
                                                <option value="Direct" selected>Direct</option>
                                                <option value="Indirect">In Direct</option>
                                            @else
                                                <option value="Direct">Direct</option>
                                                <option value="Indirect" selected>In Direct</option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-12 form-group">
                                        <h4>Off Days</h4>
                                    </div>
                                    <div class="col-md-1 form-group">
                                        <label>Mon</label>
                                        <input type="checkbox" id='monday' name="monday" value="1"
                                               {{$data['monday'] ? 'checked' :''}} class="">
                                    </div>
                                    <div class="col-md-1 form-group">
                                        <label>Tue</label>
                                        <input type="checkbox" id='tuesday' name="tuesday" value="1"
                                               {{$data['tuesday'] ? 'checked' :''}} class="">
                                    </div>
                                    <div class="col-md-1 form-group">
                                        <label>Wed</label>
                                        <input type="checkbox" id='wednesday' name="wednesday" value="1"
                                               {{$data['wednesday'] ? 'checked' :''}} class="">
                                    </div>
                                    <div class="col-md-1 form-group">
                                        <label>Thu</label>
                                        <input type="checkbox" id='thursday' name="thursday" value="1"
                                               {{$data['thursday'] ? 'checked' :''}} class="">
                                    </div>
                                    <div class="col-md-1 form-group">
                                        <label>Fri</label>
                                        <input type="checkbox" id='friday' name="friday" value="1"
                                               {{$data['friday'] ? 'checked' :''}} class="">
                                    </div>
                                    <div class="col-md-1 form-group">
                                        <label>Sat</label>
                                        <input type="checkbox" id='saturday' name="saturday" value="1"
                                               {{$data['saturday'] ? 'checked' :''}} class="">
                                    </div>
                                    <div class="col-md-1 form-group">
                                        <label>Sun</label>
                                        <input type="checkbox" id='sunday' name="sunday" value="1"
                                               {{$data['sunday'] ? 'checked' :''}} class="">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Monday In Date</label>
                                        <input type="time" id='monday_in_date'
                                               value="{{ $data['monday_in_date'] ? date('H:i',strtotime($data['monday_in_date'])) : '' }}"
                                               name="monday_in_date" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Monday Out Date</label>
                                        <input type="time" id='monday_out_date'
                                               value="{{ $data['monday_out_date'] ? date('H:i',strtotime($data['monday_out_date'])) : '' }}"
                                               name="monday_out_date" class="form-control">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Tuesday In Date</label>
                                        <input type="time" id='tuesday_in_date'
                                               value="{{ $data['tuesday_in_date'] ? date('H:i',strtotime($data['tuesday_in_date'])) : '' }}"
                                               name="tuesday_in_date" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Tuesday Out Date</label>
                                        <input type="time" id='tuesday_out_date'
                                               value="{{ $data['tuesday_out_date'] ? date('H:i',strtotime($data['tuesday_out_date'])) : '' }}"
                                               name="tuesday_out_date" class="form-control">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Wednesday In Date</label>
                                        <input type="time" id='wednesday_in_date'
                                               value="{{ $data['wednesday_in_date'] ? date('H:i',strtotime($data['wednesday_in_date'])) : '' }}"
                                               name="wednesday_in_date" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Wednesday Out Date</label>
                                        <input type="time" id='wednesday_out_date'
                                               value="{{ $data['wednesday_out_date'] ? date('H:i',strtotime($data['wednesday_out_date'])) : '' }}"
                                               name="wednesday_out_date" class="form-control">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Thursday In Date</label>
                                        <input type="time" id='thursday_in_date'
                                               value="{{ $data['thursday_in_date'] ? date('H:i',strtotime($data['thursday_in_date'])) : '' }}"
                                               name="thursday_in_date" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Thursday Out Date</label>
                                        <input type="time" id='thursday_out_date'
                                               value="{{ $data['thursday_out_date'] ? date('H:i',strtotime($data['thursday_out_date'])) : '' }}"
                                               name="thursday_out_date" class="form-control">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Friday In Date</label>
                                        <input type="time" id='friday_in_date'
                                               value="{{ $data['friday_in_date'] ? date('H:i',strtotime($data['friday_in_date'])) : '' }}"
                                               name="friday_in_date" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Friday Out Date</label>
                                        <input type="time" id='friday_out_date'
                                               value="{{ $data['friday_out_date'] ? date('H:i',strtotime($data['friday_out_date'])) : '' }}"
                                               name="friday_out_date" class="form-control">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Saturday In Date</label>
                                        <input type="time" id='saturday_in_date'
                                               value="{{ $data['saturday_in_date'] ? date('H:i',strtotime($data['saturday_in_date'])) : '' }}"
                                               name="saturday_in_date" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Saturday Out Date</label>
                                        <input type="time" id='saturday_out_date'
                                               value="{{ $data['saturday_out_date'] ? date('H:i',strtotime($data['saturday_out_date'])) : '' }}"
                                               name="saturday_out_date" class="form-control">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Sunday In Date</label>
                                        <input type="time" id='sunday_in_date'
                                               value="{{ $data['sunday_in_date'] ? date('H:i',strtotime($data['sunday_in_date'])) : '' }}"
                                               name="sunday_in_date" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Sunday Out Date</label>
                                        <input type="time" id='sunday_out_date'
                                               value="{{ $data['sunday_out_date'] ? date('H:i',strtotime($data['sunday_out_date'])) : '' }}"
                                               name="sunday_out_date" class="form-control">
                                    </div>

                                    <div class="col-md-12 form-group mt-2">
                                        <center>
                                            <input type="submit" name="submit" value="Update" class="btn btn-success">
                                        </center>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane p-3" id="section-linemove-2" role="tabpanel">
                            <form action="{{ route('edi_tbl_user.store', [ 'id' => isset($data['id']) ? $data['id'] : 0 , 'dataType' => 'education_data']) }}"
                                  method="post">
                                <input type="hidden" name="user_id" value="{{isset($data['id']) ? $data['id']: 0}}"/>
                                @csrf

                                {{-- added on 04-05-2025 for grid view like old --}}
                                <div class="tableView">
                                    <div class="table-reponsive">
                                        <table class="table table-strriped" width="100%" id="pastEducation">
                                            <thead>
                                                <tr>
                                                    <th>Degree</th>
                                                    <th>Medium</th>
                                                    <th>University Name</th>
                                                    <th>Passing Year</th>
                                                    <th>Main subject</th>
                                                    <th>Secondary Subject</th>
                                                    <th>Percentage</th>
                                                    <th>CPI</th>
                                                    <th>CGPA</th>
                                                    <th>Remarks</th>
                                                    <th class="text-left">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($past_educations as $past_education)
                                                <input type="hidden" name="past_education_id[]" value="{{$past_education->id ?? 0}}"/>
                                                <tr>
                                                    <td>
                                                        <input type="text" name="degree[]" value="@if(isset($past_education->degree)){{$past_education->degree}}@endif" class="form-control mb-0" data-new="1" placeholder="Degree">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="medium[]" value="@if(isset($past_education->medium)){{$past_education->medium}}@endif" class="form-control mb-0" data-new="1" placeholder="Medium">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="university_name[]" value="@if(isset($past_education->university_name)){{$past_education->university_name}}@endif" class="form-control mb-0" data-new="1" placeholder="university name">
                                                    </td>
                                                    <td>
                                                         <input type="text" name="passing_year[]" value="@if(isset($past_education->passing_year)){{$past_education->passing_year}}@endif" class="form-control mb-0" data-new="1" placeholder="Passing Year">
                                                    </td>
                                                    <td>
                                                        <select name="main_subject[]" id="main_subject[]"
                                                        class="form-control">
                                                            <option value="0">Select Subject</option>
                                                            @if(!empty($sub_std_map))
                                                                @foreach($sub_std_map as $key => $val)
                                                                    <option value="{{ $val->id }}" @if(isset($past_education->main_subject) &&  $val->id == $past_education->main_subject) Selected @endif>{{ $val->display_name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </td>
                                                    <td>
                                                         <input type="text" name="secondary_subject[]" value="@if(isset($past_education->secondary_subject)){{$past_education->secondary_subject}}@endif" class="form-control mb-0" data-new="1" placeholder="Secondary Subject">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="percentage[]" value="@if(isset($past_education->percentage)){{$past_education->percentage}}@endif" class="form-control mb-0" data-new="1" placeholder="Percentage">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="cpi[]" value="@if(isset($past_education->cpi)){{$past_education->cpi}}@endif" class="form-control mb-0" data-new="1" placeholder="CPI">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="cgpa[]" value="@if(isset($past_education->cgpa)){{$past_education->cgpa}}@endif" class="form-control mb-0" data-new="1" placeholder="CGPA">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="remarks[]" value="@if(isset($past_education->remarks)){{$past_education->remarks}}@endif" class="form-control mb-0" data-new="1" placeholder="Remarks">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger" style="padding:4px 6px" onclick="deleteData('pastEducation','tbluser_past_educations',{{$past_education->id}});">
                                                            <span class="mdi mdi-delete"></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td>
                                                        <input type="text" name="degree[]" class="form-control mb-0" data-new="1" placeholder="Degree">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="medium[]" class="form-control mb-0" data-new="1" placeholder="Medium">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="university_name[]" class="form-control mb-0" data-new="1" placeholder="university name">
                                                    </td>
                                                    <td>
                                                         <input type="text" name="passing_year[]" class="form-control mb-0" data-new="1" placeholder="Passing Year">
                                                    </td>
                                                    <td>
                                                        <select name="main_subject[]" id="main_subject[]"
                                                        class="form-control">
                                                            <option value="0">Select Subject</option>
                                                            @if(!empty($sub_std_map))
                                                                @foreach($sub_std_map as $key => $val)
                                                                    <option value="{{ $val->id }}">{{ $val->display_name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </td>
                                                    <td>
                                                         <input type="text" name="secondary_subject[]" class="form-control mb-0" data-new="1" placeholder="Secondary Subject">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="percentage[]" class="form-control mb-0" data-new="1" placeholder="Percentage">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="cpi[]" class="form-control mb-0" data-new="1" placeholder="CPI">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="cgpa[]" class="form-control mb-0" data-new="1" placeholder="CGPA">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="remarks[]" class="form-control mb-0" data-new="1" placeholder="Remarks">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-success addRow" data-tableId="pastEducation">
                                                            <span class="mdi mdi-plus"></span>
                                                        </button>
                                                        <button type="button" class="btn btn-danger removeRow d-none">
                                                            <span class="mdi mdi-minus"></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                {{-- end grid view old 07-05-2025 --}}

                                <div class="col-md-12 form-group mt-2">
                                    <center>
                                        <input type="submit" name="submit" value="Update" class="btn btn-success">
                                    </center>
                                </div>

                            </form>
                        </div>
                        <div class="tab-pane p-3" id="section-linemove-3" role="tabpanel">
                            <form action="{{ route('edi_tbl_user.store', [ 'id' => isset($data['id']) ? $data['id'] : 0 , 'dataType' => 'experience_detail'] ) }}"
                                  method="post">
                                <input type="hidden" name="user_id" value="{{isset($data['id']) ? $data['id']: 0}}"/>
                                @csrf
                                @php 
                                $teachingType = [1=>"Teaching",2=>"Non-Teaching"];
                                $experienceType = ["School Exp."=>"School Exp.","Diploma Exp."=>"Diploma Exp.","Degree Exp."=>"Degree Exp.",    "Industrial Exp."=>"Industrial Exp."];
                                @endphp

                                {{-- added on 08-05-2025 for grid view like old --}}
                                <div class="tableView">
                                    <div class="table-reponsive">
                                        <table class="table table-strriped" width="100%" id="experienceDetails">
                                            <thead>
                                                <tr>
                                                    <th>Teching Type</th>
                                                    <th>Institute Name</th>
                                                    <th>Designation</th>
                                                    <th>Exp.Type</th>
                                                    <th>Joining Date</th>
                                                    <th>Leaving Date</th>
                                                    <th>Experience</th>
                                                    <th>Remarks</th>
                                                    <th class="text-left">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($experience_details as $experience_detail)
                                                <input type="hidden" name="experience_detail_id[]" value="{{$experience_detail->id}}">
                                                <tr>
                                                    <td>
                                                        <select name="teching_type[]" id="teching_type" class="form-control"  data-new="1">
                                                            <option value="">N/A</option>
                                                            @foreach($teachingType as $k=>$v)
                                                            <option value="{{$k}}" @if(isset($experience_detail->teching_type) && $experience_detail->teching_type==$k) selected @endif>{{$v}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="institutional_name[]" value="@if(isset($experience_detail->institutional_name)){{$experience_detail->institutional_name}}@endif" class="form-control mb-0" data-new="1">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="designation_name[]" value="@if(isset($experience_detail->designation_name)){{$experience_detail->designation_name}}@endif" class="form-control mb-0" data-new="1">
                                                    </td>
                                                    <td>
                                                        <select name="experience_type[]" id="experience_type" class="form-control"  data-new="1">
                                                            <option value="">N/A</option>
                                                            @foreach($experienceType as $k=>$v)
                                                            <option value="{{$k}}" @if(isset($experience_detail->experience_type) && $experience_detail->experience_type==$k) selected @endif>{{$v}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control mydatepicker" name="joining_date[]" placeholder="dd/mm/yyyy" value="@if(isset($experience_detail->joining_date)){{$experience_detail->joining_date}}@endif" autocomplete="off" data-new="1"><span class="input-group-addon"><i class="icon-calender"></i></span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control mydatepicker" name="leaving_date[]" placeholder="dd/mm/yyyy" value="@if(isset($experience_detail->leaving_date)){{$experience_detail->leaving_date}}@endif" autocomplete="off" data-new="1" onchange="validateDates(this)"><span class="input-group-addon"><i class="icon-calender"></i></span>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="experience[]" value="@if(isset($experience_detail->experience)){{$experience_detail->experience}}@endif" class="form-control mb-0" data-new="1">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="remarks[]" value="@if(isset($experience_detail->remarks)){{$experience_detail->remarks}}@endif"
                                                        class="form-control mb-0" data-new="1">
                                                    </td>
                                                  
                                                    <td>
                                                        <button type="button" class="btn btn-danger" style="padding:4px 6px" onclick="deleteData('experienceDetails','tbluser_experience_details',{{$experience_detail->id}});">
                                                            <span class="mdi mdi-delete"></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                                <input type="hidden" name="experience_detail_id[]" value="0">
                                                <tr>
                                                    <td>
                                                        <select name="teching_type[]" id="teching_type" class="form-control"  data-new="1">
                                                            <option value="">N/A</option>
                                                            @foreach($teachingType as $k=>$v)
                                                            <option value="{{$k}}">{{$v}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="institutional_name[]" class="form-control mb-0" data-new="1">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="designation_name[]" class="form-control mb-0" data-new="1">
                                                    </td>
                                                    <td>
                                                        <select name="experience_type[]" id="experience_type" class="form-control"  data-new="1">
                                                            <option value="">N/A</option>
                                                            @foreach($experienceType as $k=>$v)
                                                            <option value="{{$k}}">{{$v}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control mydatepicker" name="joining_date[]" placeholder="dd/mm/yyyy" autocomplete="off" data-new="1" onchange=" (this)"><span class="input-group-addon"><i class="icon-calender"></i></span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control mydatepicker" name="leaving_date[]" placeholder="dd/mm/yyyy" autocomplete="off" data-new="1" onchange="validateDates(this)"><span class="input-group-addon"><i class="icon-calender"></i></span>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="experience[]" class="form-control mb-0" data-new="1">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="remarks[]" class="form-control mb-0" data-new="1">
                                                    </td>
                                                  
                                                    <td>
                                                        <button type="button" class="btn btn-success addRow" data-tableId="experienceDetails">
                                                            <span class="mdi mdi-plus"></span>
                                                        </button>
                                                        <button type="button" class="btn btn-danger removeRow d-none">
                                                            <span class="mdi mdi-minus"></span>
                                                        </button>
                                                    </td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            {{-- added data ends  08-05-2025 --}}
                                
                                <div class="col-md-12 form-group mt-2">
                                    <center>
                                        <input type="submit" name="submit" value="Update" class="btn btn-success">
                                    </center>
                                </div>

                            </form>
                        </div>
                        <div class="tab-pane p-3" id="section-linemove-4" role="tabpanel">
                            <form action="{{ route('edi_tbl_user.store', [ 'id' => isset($data['id']) ? $data['id'] : 0 , 'dataType' => 'training_details'] ) }}"
                                  method="post">
                                <input type="hidden" name="user_id" value="{{isset($data['id']) ? $data['id']: 0}}"/>
                                @csrf
                                {{-- added data ends  08-05-2025 --}}
                                 <div class="table-responsive">
                                    <table class="table table-stripped" id="trainningDetails">
                                        <thead>
                                            <tr>
                                                <th>Training Name</th>
                                                <th>Training Subject</th>
                                                <th>Training Place</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Days</th>
                                                <th>Remarks</th>
                                                <th class="text-left">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($training_details as $training_detail)
                                                <input type="hidden" name="training_detail_id[]" value="{{$training_detail->id}}">
                                                <tr>
                                                    <td> 
                                                        <input type="text" name="training_name[]" value="@if(isset($training_detail->training_name)){{$training_detail->training_name}}@endif" class="form-control mb-0" data-new="1">
                                                    </td>
                                                     <td>
                                                        <input type="text" name="training_subject[]" value="@if(isset($training_detail->training_subject)){{$training_detail->training_subject}}@endif" class="form-control mb-0" data-new="1">
                                                     </td>
                                                    <td>
                                                        <input type="text" name="training_place[]" value="@if(isset($training_detail->training_place)){{$training_detail->training_place}}@endif" class="form-control mb-0" data-new="1">
                                                    </td>
                                                    <td>
                                                        <input type="date" name="start_date[]" value="@if(isset($training_detail->start_date)){{$training_detail->start_date}}@endif" class="form-control mb-0" data-new="1">
                                                    </td>
                                                    <td>
                                                        <input type="date" name="end_date[]" value="@if(isset($training_detail->end_date)){{$training_detail->end_date}}@endif" class="form-control mb-0" data-new="1" onchange="validateDays(this)">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="days[]" value="@if(isset($training_detail->days)){{$training_detail->days}}@endif" class="form-control mb-0" data-new="1">
                                                    </td>
                                                     <td> 
                                                        <input type="text" name="remarks[]" value="@if(isset($training_detail->remarks)){{$training_detail->remarks}}@endif" class="form-control mb-0" data-new="1">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger" style="padding:4px 6px" onclick="deleteData('trainningDetails','tbluser_training_details',{{$training_detail->id}});">
                                                            <span class="mdi mdi-delete"></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <input type="hidden" name="training_detail_id[]" value="0">
                                                <tr>
                                                    <td> 
                                                        <input type="text" name="training_name[]" class="form-control mb-0" data-new="1">
                                                    </td>
                                                     <td>
                                                        <input type="text" name="training_subject[]" class="form-control mb-0" data-new="1">
                                                     </td>
                                                    <td>
                                                        <input type="text" name="training_place[]" class="form-control mb-0" data-new="1">
                                                    </td>
                                                    <td>
                                                        <input type="date" name="start_date[]" class="form-control mb-0" data-new="1">
                                                    </td>
                                                    <td>
                                                        <input type="date" name="end_date[]" class="form-control mb-0" data-new="1" onchange="validateDays(this)">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="days[]" class="form-control mb-0" data-new="1">
                                                    </td>
                                                     <td> 
                                                        <input type="text" name="remarks[]" class="form-control mb-0" data-new="1">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-success addRow" data-tableId="trainningDetails">
                                                            <span class="mdi mdi-plus"></span>
                                                        </button>
                                                        <button type="button" class="btn btn-danger removeRow d-none">
                                                            <span class="mdi mdi-minus"></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                        </tbody>
                                    </table>
                                 </div>
                                {{-- added data ends  08-05-2025 --}}
                                
                                <div class="col-md-12 form-group mt-2">
                                    <center>
                                        <input type="submit" name="submit" value="Update" class="btn btn-success">
                                    </center>
                                </div>

                            </form>
                        </div>
                        <div class="tab-pane p-3" id="section-linemove-5" role="tabpanel">
                            <form action="{{ route('edi_tbl_user.store', [ 'id' => isset($data['id']) ? $data['id'] : 0 , 'dataType' => 'professional_details'] ) }}"
                                  method="post">
                                <input type="hidden" name="user_id" value="{{isset($data['id']) ? $data['id']: 0}}"/>
                                @csrf

                                @php 
                                    $appType=[1=>"REGULAR",2=>"ADHOC",3=>"CONTRACT",4=>"VISITING"];
                                    $doctorate = [1=>'PH.D'];
                                    $pgDegree = [2=>"M.phil.",3=>"M.com",4=>"M.B.A",5=>"M.C.A",6=>"M.Sc",10=>"M.E.",17=>"M.Ed.",11=>"M.A",15=>"M.Tech."];
                                    $ugDegree=[7=>"B.B.A",8=>"B.com",9=>"B.C.A",12=>"B.A",13=>"B.Sc.",14=>"B.E.",16=>"B.Tech.",18=>"Doploma"];
                                @endphp                
                                @foreach($professional_details as $professional_detail)
                                    <div class="col-md-12 form-group">
                                        <div class="addButtonCheckboxProfessional">
                                            <div class="row align-items-center professional_detail_id_{{$professional_detail->id}}">
                                                <input type="hidden" name="professional_detail_id[]"
                                                       value="{{$professional_detail->id}}">
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label for="control-label">Designation</label>
                                                        <input type="text" name="designation[]"
                                                               value="@if(isset($professional_detail->designation)){{$professional_detail->designation}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label for="control-label">Appointment Type</label>
                                                        {{-- <input type="text" name="appointment_type[]"
                                                               value="@if(isset($professional_detail->appointment_type)){{$professional_detail->appointment_type}}@endif"
                                                               class="form-control mb-0" data-new="1">--}}
                                                        <select name="appointment_type[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                            @foreach($appType as $k=>$v)
                                                            <option value="{{$k}}" @if(isset($professional_detail->appointment_type) && $professional_detail->appointment_type==$k) Selected @endif>{{$v}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Doctorate Degree</label>
                                                        {{--<input type="text" name="doctorate_degree[]"
                                                               value="@if(isset($professional_detail->doctorate_degree)){{$professional_detail->doctorate_degree}}@endif"
                                                               class="form-control mb-0" data-new="1">--}}
                                                        <select name="doctorate_degree[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                            @foreach($doctorate as $k=>$v)
                                                            <option value="{{$k}}" @if(isset($professional_detail->doctorate_degree) && $professional_detail->doctorate_degree==$k) selected @endif>{{$v}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Doctorate Degree Percentage</label>
                                                        <input type="text" name="doctorate_degree_percentage[]"
                                                               value="@if(isset($professional_detail->doctorate_degree_percentage)){{$professional_detail->doctorate_degree_percentage}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>PG Degree</label>
                                                        {{--<input type="text" name="pg_degree[]"
                                                               value="@if(isset($professional_detail->pg_degree)){{$professional_detail->pg_degree}}@endif"
                                                               class="form-control mb-0" data-new="1">--}}
                                                        <select name="pg_degree[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                            @foreach($pgDegree as $k=>$v)
                                                            <option value="{{$k}}" @if(isset($professional_detail->pg_degree) && $professional_detail->pg_degree==$k) selected @endif>{{$v}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>PG Degree Percentage</label>
                                                        <input type="text" name="pg_degree_percentage[]"
                                                               value="@if(isset($professional_detail->pg_degree_percentage)){{$professional_detail->pg_degree_percentage}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>UG Degree</label>
                                                        {{--<input type="text" name="ug_degree[]"
                                                               value="@if(isset($professional_detail->ug_degree)){{$professional_detail->ug_degree}}@endif"
                                                               class="form-control mb-0" data-new="1">--}}
                                                            <select name="ug_degree[]" class="form-control mb-0" data-new="1">
                                                                <option value="">N/A</option>
                                                                @foreach($ugDegree as $k=>$v)
                                                                <option value="{{$k}}" @if(isset($professional_detail->ug_degree) && $professional_detail->ug_degree) selected @endif>{{$v}}</option>
                                                                @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>UG Degree Percentage</label>
                                                        <!-- <input type="text" name="ug_degree_percentage[]"
                                                               value="@if(isset($professional_detail->ug_degree_percentage)){{$professional_detail->ug_degree_percentage}}@endif"
                                                               class="form-control mb-0" data-new="1"> -->
                                                               <select name="ug_degree[]" class="form-control mb-0" data-new="1">
                                                                <option value="">N/A</option>
                                                                @foreach($ugDegree as $k=>$v)
                                                                <option value="{{$k}}">{{$v}}</option>
                                                                @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Other Qualification</label>
                                                        <input type="text" name="other_qualification[]"
                                                               value="@if(isset($professional_detail->other_qualification)){{$professional_detail->other_qualification}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Other Qualification Percentage</label>
                                                        <input type="text" name="other_qualification_percentage[]"
                                                               value="@if(isset($professional_detail->other_qualification_percentage)){{$professional_detail->other_qualification_percentage}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Specification</label>
                                                        <input type="text" name="specification[]"
                                                               value="@if(isset($professional_detail->specification)){{$professional_detail->specification}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>National Publication</label>
                                                        {{--<input type="text" name="national_publication[]"
                                                               value="@if(isset($professional_detail->national_publication)){{$professional_detail->national_publication}}@endif"
                                                               class="form-control mb-0" data-new="1">--}}
                                                               <select name="national_publication[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                             @for($i=1;$i<=50;$i++)
                                                             <option value="{{$i}}" @if(isset($professional_detail->national_publication) && $professional_detail->national_publication==$i) selected @endif>{{$i}}</option>
                                                            @endfor
                                                               </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>International Publication</label>
                                                        {{--<input type="text" name="international_publication[]"
                                                               value="@if(isset($professional_detail->international_publication)){{$professional_detail->international_publication}}@endif"
                                                               class="form-control mb-0" data-new="1">--}}
                                                            <select name="international_publication[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                             @for($i=1;$i<=50;$i++)
                                                             <option value="{{$i}}" @if(isset($professional_detail->international_publication) && $professional_detail->international_publication==$i) selected @endif>{{$i}}</option>
                                                            @endfor
                                                               </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>No Of Books Published</label>
                                                       {{--<input type="text" name="no_of_books_published[]"
                                                               value="@if(isset($professional_detail->no_of_books_published)){{$professional_detail->no_of_books_published}}@endif"
                                                               class="form-control mb-0" data-new="1">--}}

                                                               <select name="no_of_books_published[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                             @for($i=1;$i<=50;$i++)
                                                             <option value="{{$i}}" @if(isset($professional_detail->no_of_books_published) && $professional_detail->no_of_books_published==$i) selected @endif>{{$i}}</option>
                                                            @endfor
                                                               </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>No of Patents</label>
                                                        {{--<input type="text" name="no_of_patents[]"
                                                               value="@if(isset($professional_detail->no_of_patents)){{$professional_detail->no_of_patents}}@endif"
                                                               class="form-control mb-0" data-new="1">--}}
                                                               <select name="no_of_patents[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                             @for($i=1;$i<=50;$i++)
                                                             <option value="{{$i}}" @if(isset($professional_detail->no_of_patents) && $professional_detail->no_of_patents==$i) selected @endif>{{$i}}</option>
                                                            @endfor
                                                               </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Teaching Experience</label>
                                                        <input type="text" name="teaching_experience[]"
                                                               value="@if(isset($professional_detail->teaching_experience)){{$professional_detail->teaching_experience}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Total Work Experience</label>
                                                        <input type="text" name="total_work_experience[]"
                                                               value="@if(isset($professional_detail->total_work_experience)){{$professional_detail->total_work_experience}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Research Experience</label>
                                                        {{--<input type="text" name="research_experience[]"
                                                               value="@if(isset($professional_detail->research_experience)){{$professional_detail->research_experience}}@endif"
                                                               class="form-control mb-0" data-new="1">--}}
                                                        <select name="research_experience[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                             @for($i=1;$i<=50;$i++)
                                                             <option value="{{$i}}" @if(isset($professional_detail->research_experience) && $professional_detail->research_experience==$i) selected @endif>{{$i}}</option>
                                                            @endfor
                                                               </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>No of projects Guided</label>
                                                        {{--<input type="text" name="no_of_projects_guided[]"
                                                               value="@if(isset($professional_detail->no_of_projects_guided)){{$professional_detail->no_of_projects_guided}}@endif"
                                                               class="form-control mb-0" data-new="1">--}}

                                                               <select name="no_of_projects_guided[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                             @for($i=1;$i<=50;$i++)
                                                             <option value="{{$i}}" @if(isset($professional_detail->no_of_projects_guided) && $professional_detail->no_of_projects_guided==$i) selected @endif>{{$i}}</option>
                                                            @endfor
                                                               </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>No of Doctorate Students Guided</label>
                                                        {{--<input type="text" name="no_of_doctorate_students_guided[]"
                                                               value="@if(isset($professional_detail->no_of_doctorate_students_guided)){{$professional_detail->no_of_doctorate_students_guided}}@endif"
                                                               class="form-control mb-0" data-new="1">--}}
                                                               <select name="no_of_doctorate_students_guided[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                             @for($i=1;$i<=50;$i++)
                                                             <option value="{{$i}}" @if(isset($professional_detail->no_of_doctorate_students_guided) && $professional_detail->no_of_doctorate_students_guided==$i) selected @endif>{{$i}}</option>
                                                            @endfor
                                                               </select>
                                                    </div>
                                                </div>


                                                <div class="col-md-1 mt-3"><a href="javascript:void(0);"
                                                                              onclick="removeProfessional({{$professional_detail->id}});"
                                                                              class="d-inline btn btn-danger"><i
                                                                class="mdi mdi-minus"></i></a></div>
                                            </div>
                                        </div>
                                    </div>

                                @endforeach

                                <div class="col-md-12 form-group">
                                    <div class="addButtonCheckboxProfessional">
                                        <div class="row align-items-center">
                                            <input type="hidden" name="professional_detail_id[]"
                                                   value="0">
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label for="control-label">Designation</label>
                                                    <input type="text" name="designation[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label for="control-label">Appointment Type</label>
                                                    <!-- <input type="text" name="appointment_type[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1"> -->
                                                           <select name="appointment_type[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                            @foreach($appType as $k=>$v)
                                                            <option value="{{$k}}">{{$v}}</option>
                                                            @endforeach
                                                        </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Doctorate Degree</label>
                                                    <!-- <input type="text" name="doctorate_degree[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1"> -->
                                                           <select name="doctorate_degree[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                            @foreach($doctorate as $k=>$v)
                                                            <option value="{{$k}}">{{$v}}</option>
                                                            @endforeach
                                                        </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Doctorate Degree Percentage</label>
                                                    <input type="text" name="doctorate_degree_percentage[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>PG Degree</label>
                                                    <!-- <input type="text" name="pg_degree[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1"> -->
                                                           <select name="pg_degree[]" class="form-control mb-0" data-new="1">
                                                            <option value="">N/A</option>
                                                            @foreach($pgDegree as $k=>$v)
                                                            <option value="{{$k}}">{{$v}}</option>
                                                            @endforeach
                                                        </select>
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>PG Degree Percentage</label>
                                                    <input type="text" name="pg_degree_percentage[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>UG Degree</label>
                                                    <input type="text" name="ug_degree[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>UG Degree Percentage</label>
                                                    <input type="text" name="ug_degree_percentage[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Other Qualification</label>
                                                    <input type="text" name="other_qualification[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Other Qualification Percentage</label>
                                                    <input type="text" name="other_qualification_percentage[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Specification</label>
                                                    <input type="text" name="specification[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>National Publication</label>
                                                    <!-- <input type="text" name="national_publication[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1"> -->
                                                        <select name="national_publication[]" class="form-control mb-0" data-new="1">
                                                        <option value="">N/A</option>
                                                            @for($i=1;$i<=50;$i++)
                                                            <option value="{{$i}}">{{$i}}</option>
                                                            @endfor
                                                            </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>International Publication</label>
                                                    <!-- <input type="text" name="international_publication[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1"> -->
                                                           <select name="international_publication[]" class="form-control mb-0" data-new="1">
                                                        <option value="">N/A</option>
                                                            @for($i=1;$i<=50;$i++)
                                                            <option value="{{$i}}">{{$i}}</option>
                                                            @endfor
                                                            </select>
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>No Of Books Published</label>
                                                    <!-- <input type="text" name="no_of_books_published[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1"> -->
                                                           <select name="no_of_books_published[]" class="form-control mb-0" data-new="1">
                                                        <option value="">N/A</option>
                                                            @for($i=1;$i<=50;$i++)
                                                            <option value="{{$i}}">{{$i}}</option>
                                                            @endfor
                                                            </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>No of Patents</label>
                                                    <!-- <input type="text" name="no_of_patents[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1"> -->
                                                           <select name="no_of_patents[]" class="form-control mb-0" data-new="1">
                                                        <option value="">N/A</option>
                                                            @for($i=1;$i<=50;$i++)
                                                            <option value="{{$i}}">{{$i}}</option>
                                                            @endfor
                                                            </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Teaching Experience</label>
                                                    <input type="text" name="teaching_experience[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Total Work Experience</label>
                                                    <input type="text" name="total_work_experience[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Research Experience</label>
                                                    <!-- <input type="text" name="research_experience[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1"> -->
                                                           <select name="research_experience[]" class="form-control mb-0" data-new="1">
                                                        <option value="">N/A</option>
                                                            @for($i=1;$i<=50;$i++)
                                                            <option value="{{$i}}">{{$i}}</option>
                                                            @endfor
                                                            </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>No of projects Guided</label>
                                                    <!-- <input type="text" name="no_of_projects_guided[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1"> -->
                                                           <select name="no_of_projects_guided[]" class="form-control mb-0" data-new="1">
                                                        <option value="">N/A</option>
                                                            @for($i=1;$i<=50;$i++)
                                                            <option value="{{$i}}">{{$i}}</option>
                                                            @endfor
                                                            </select>
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>No of Doctorate Students Guided</label>
                                                    <!-- <input type="text" name="no_of_doctorate_students_guided[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1"> -->
                                                           <select name="no_of_projects_guided[]" class="form-control mb-0" data-new="1">
                                                        <option value="">N/A</option>
                                                            @for($i=1;$i<=50;$i++)
                                                            <option value="{{$i}}">{{$i}}</option>
                                                            @endfor
                                                            </select>
                                                </div>
                                            </div>


                                            <div class="col-md-1 mt-3">
                                                <a href="javascript:void(0);" onclick="addNewRowWithProfessional();"
                                                   class="d-inline-block btn btn-success mr-2"><i
                                                            class="mdi mdi-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 form-group mt-2">
                                    <center>
                                        <input type="submit" name="submit" value="Update" class="btn btn-success">
                                    </center>
                                </div>

                            </form>
                        </div>
                        <div class="tab-pane p-3" id="section-linemove-6" role="tabpanel">
                            <form action="{{ route('edi_tbl_user.store', [ 'id' => isset($data['id']) ? $data['id'] : 0 , 'dataType' => 'salary_details'] ) }}"
                                  method="post">
                                <input type="hidden" name="user_id" value="{{isset($data['id']) ? $data['id']: 0}}"/>
                                @csrf

                                @php 
                                    $payslip = [2=>"1-10",1=>"10-15"];
                                    $salaryMode = [1=>"Cash",2=>"Cheque",3=>"DD",4=>"Online Transfer"];
                                @endphp                  
                                @foreach($salary_details as $salary_detail)
                                    <div class="col-md-12 form-group">
                                        <div class="addButtonCheckboxSalary">
                                            <div class="row align-items-center salary_detail_id_{{$salary_detail->id}}">
                                                <input type="hidden" name="salary_detail_id[]"
                                                       value="{{$salary_detail->id}}">
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label for="control-label">Pay Scale</label>
                                                       {{--<input type="text" name="pay_scale[]"
                                                               value="@if(isset($salary_detail->pay_scale)){{$salary_detail->pay_scale}}@endif"
                                                               class="form-control mb-0" data-new="1">--}} 
                                                               <select name="pay_scale[]" id="pay_scale" class="form-control mb-0" data-new="1" >
                                                                <option value="">N/A</option>
                                                                @foreach($payslip as $k=>$v)
                                                                <option value="{{$k}}" @if(isset($salary_detail->pay_scale) && $salary_detail->pay_scale==$k) Selected @endif>{{$v}}</option>
                                                                @endforeach
                                                               </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label for="control-label">Increment Date</label>
                                                        <input type="date" name="increment_date[]"
                                                               value="@if(isset($salary_detail->increment_date)){{$salary_detail->increment_date}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Salary Mode</label>
                                                        {{--<input type="text" name="salary_mode[]"
                                                               value="@if(isset($salary_detail->salary_mode)){{$salary_detail->salary_mode}}@endif"
                                                               class="form-control mb-0" data-new="1">--}}
                                                               <select  name="salary_mode[]" class="form-control mb-0" >
                                                                <option value="">N/A</option>
                                                                    @foreach($salaryMode as $k=>$v)
                                                                    <option value="{{$k}}" @if(isset($salary_detail->salary_mode) && $salary_detail->salary_mode==$k) Selected @endif>{{$v}}</option>
                                                                    @endforeach
                                                                </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Basic</label>
                                                        <input type="text" name="basic[]"
                                                               value="@if(isset($salary_detail->basic)){{$salary_detail->basic}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Grade Pay</label>
                                                        <input type="text" name="grade_pay[]"
                                                               value="@if(isset($salary_detail->grade_pay)){{$salary_detail->grade_pay}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Basic Pay</label>
                                                        <input type="text" name="basic_pay[]"
                                                               value="@if(isset($salary_detail->basic_pay)){{$salary_detail->basic_pay}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>DA</label>
                                                        <input type="text" name="da[]"
                                                               value="@if(isset($salary_detail->da)){{$salary_detail->da}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>DA Percentage</label>
                                                        <input type="text" name="da_percentage[]"
                                                               value="@if(isset($salary_detail->da_percentage)){{$salary_detail->da_percentage}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>CLA</label>
                                                        <input type="text" name="cla[]"
                                                               value="@if(isset($salary_detail->cla)){{$salary_detail->cla}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>HRA</label>
                                                        <input type="text" name="hra[]"
                                                               value="@if(isset($salary_detail->hra)){{$salary_detail->hra}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>HRA Percentage</label>
                                                        <input type="text" name="hra_percentage[]"
                                                               value="@if(isset($salary_detail->hra_percentage)){{$salary_detail->hra_percentage}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Vehicle Allowances</label>
                                                        <input type="text" name="vehicle_allowances[]"
                                                               value="@if(isset($salary_detail->vehicle_allowances)){{$salary_detail->vehicle_allowances}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Medical Allowances</label>
                                                        <input type="text" name="medical_allowances[]"
                                                               value="@if(isset($salary_detail->medical_allowances)){{$salary_detail->medical_allowances}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Other Allowances</label>
                                                        <input type="text" name="other_allowances[]"
                                                               value="@if(isset($salary_detail->other_allowances)){{$salary_detail->other_allowances}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Gross Salary</label>
                                                        <input type="text" name="gross_salary[]"
                                                               value="@if(isset($salary_detail->gross_salary)){{$salary_detail->gross_salary}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Bank Account Number</label>
                                                        <input type="text" name="bank_account_number[]"
                                                               value="@if(isset($salary_detail->bank_account_number)){{$salary_detail->bank_account_number}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Bank Name</label>
                                                        <input type="text" name="bank_name[]"
                                                               value="@if(isset($salary_detail->bank_name)){{$salary_detail->bank_name}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Bank IFSC Code</label>
                                                        <input type="text" name="bank_ifsc_code[]"
                                                               value="@if(isset($salary_detail->bank_ifsc_code)){{$salary_detail->bank_ifsc_code}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Bank Branch</label>
                                                        <input type="text" name="bank_branch[]"
                                                               value="@if(isset($salary_detail->bank_branch)){{$salary_detail->bank_branch}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label>Pf Number</label>
                                                        <input type="text" name="pf_number[]"
                                                               value="@if(isset($salary_detail->pf_number)){{$salary_detail->pf_number}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>


                                                <div class="col-md-1 mt-3"><a href="javascript:void(0);"
                                                                              onclick="removeSalary({{$salary_detail->id}});"
                                                                              class="d-inline btn btn-danger"><i
                                                                class="mdi mdi-minus"></i></a></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="col-md-12 form-group">
                                    <div class="addButtonCheckboxSalary">
                                        <div class="row align-items-center">
                                            <input type="hidden" name="salary_detail_id[]" value="0">
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label for="control-label">Pay Scale</label>
                                                    <!-- <input type="text" name="pay_scale[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1"> -->
                                                           <select name="pay_scale" id="pay_scale" class="form-control">
                                                                <option value="">N/A</option>
                                                                @foreach($payslip as $k=>$v)
                                                                <option value="{{$k}}" >{{$v}}</option>
                                                                @endforeach
                                                               </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label for="control-label">Increment Date</label>
                                                    <input type="date" name="increment_date[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Salary Mode</label>
                                                    <!-- <input type="text" name="salary_mode[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1"> -->
                                                           <select  name="salary_mode[]" class="form-control mb-0" >
                                                                <option value="">N/A</option>
                                                                    @foreach($salaryMode as $k=>$v)
                                                                    <option value="{{$k}}">{{$v}}</option>
                                                                    @endforeach
                                                                </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Basic</label>
                                                    <input type="text" name="basic[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Grade Pay</label>
                                                    <input type="text" name="grade_pay[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Basic Pay</label>
                                                    <input type="text" name="basic_pay[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>DA</label>
                                                    <input type="text" name="da[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>DA Percentage</label>
                                                    <input type="text" name="da_percentage[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>CLA</label>
                                                    <input type="text" name="cla[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>HRA</label>
                                                    <input type="text" name="hra[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>HRA Percentage</label>
                                                    <input type="text" name="hra_percentage[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Vehicle Allowances</label>
                                                    <input type="text" name="vehicle_allowances[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Medical Allowances</label>
                                                    <input type="text" name="medical_allowances[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Other Allowances</label>
                                                    <input type="text" name="other_allowances[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Gross Salary</label>
                                                    <input type="text" name="gross_salary[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Bank Account Number</label>
                                                    <input type="text" name="bank_account_number[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Bank Name</label>
                                                    <input type="text" name="bank_name[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Bank IFSC Code</label>
                                                    <input type="text" name="bank_ifsc_code[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Bank Branch</label>
                                                    <input type="text" name="bank_branch[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label>Pf Number</label>
                                                    <input type="text" name="pf_number[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>
                                            <div class="col-md-1 mt-3">
                                                <a href="javascript:void(0);" onclick="addNewRowWithSalary();"
                                                   class="d-inline-block btn btn-success mr-2"><i
                                                            class="mdi mdi-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 form-group mt-2">
                                    <center>
                                        <input type="submit" name="submit" value="Update" class="btn btn-success">
                                    </center>
                                </div>

                            </form>

                        </div>
                        <div class="tab-pane p-3" id="section-linemove-7" role="tabpanel">
                            Staff Document and General Information
                            <form action="{{ route('edi_tbl_user.store', [ 'id' => isset($data['id']) ? $data['id'] : 0 , 'dataType' => 'document_details'] ) }}"
                                  method="post" enctype="multipart/form-data">
                                <input type="hidden" name="user_id" value="{{isset($data['id']) ? $data['id']: 0}}"/>
                                @csrf


                                @foreach($document_details as $document_detail)
                                    <div class="col-md-12 form-group">
                                        <div class="addButtonCheckboxDocument">
                                            <div class="row align-items-center document_detail_id_{{$document_detail->id}}">
                                                <input type="hidden" name="document_detail_id[]"
                                                       value="{{$document_detail->id}}">
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label for="control-label">Document Title</label>
                                                        <input type="text" name="document_title[]"
                                                               value="@if(isset($document_detail->document_title)){{$document_detail->document_title}}@endif"
                                                               class="form-control mb-0" data-new="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 my-2">
                                                    <div class="form-group mb-0">
                                                        <label for="control-label">File</label>
                                                        <input type="file" name="new_file[]"
                                                               class="form-control mb-0" data-new="1">
                                                        <a href="https://s3-triz.fra1.cdn.digitaloceanspaces.com/public/he_staff_document/{{$document_detail->file}}" target="_blank" style="color:blue">view</a>
                                                    </div>
                                                </div>

                                                <div class="col-md-1 mt-3"><a href="javascript:void(0);"
                                                                              onclick="removeDocument({{$document_detail->id}});"
                                                                              class="d-inline btn btn-danger"><i
                                                                class="mdi mdi-minus"></i></a></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="col-md-12 form-group">
                                    <div class="addButtonCheckboxDocument">
                                        <div class="row align-items-center">
                                            <input type="hidden" name="document_detail_id[]" value="0">
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label for="control-label">Document Title</label>
                                                    <input type="text" name="document_title[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>
                                            <div class="col-md-2 my-2">
                                                <div class="form-group mb-0">
                                                    <label for="control-label">File</label>
                                                    <input type="file" name="file[]"
                                                           value=""
                                                           class="form-control mb-0" data-new="1">
                                                </div>
                                            </div>
                                            <div class="col-md-1 mt-3">
                                                <a href="javascript:void(0);" onclick="addNewRowWithDocument();"
                                                   class="d-inline-block btn btn-success mr-2"><i
                                                            class="mdi mdi-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 form-group mt-2">
                                    <center>
                                        <input type="submit" name="submit" value="Update" class="btn btn-success">
                                    </center>
                                </div>

                            </form>
                        </div>
                        <div class="tab-pane p-3" id="section-linemove-8" role="tabpanel">
                            @include('lms.triz_skills')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footerJs')
<script src="../../../admin_dep/js/cbpFWTabs.js"></script>
<script type="text/javascript">
    console.log("aaaaa");

    function addNewRowWithChain() {
        data_new = 1;
        var htmlcontent = '';
        htmlcontent += '<div class="clearfix"></div><div class="addButtonCheckboxEducation" style="display: flex; margin-right: -15px; margin-left: -15px; flex-wrap: wrap;"><input type="hidden" name="past_education_id[]" value="0">';

        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Degree</label><input type="text" name="degree[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Medium</label><input type="text" name="medium[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">University Name</label><input type="text" name="university_name[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Passing Year</label><input type="text" name="passing_year[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += `<div class="col-md-2 my-2"><div class="form-group mb-0">
                       <label for="main_subject[]">Main Subject</label>
                        <select name="main_subject[]" id="main_subject[]" class="form-control">
                            <option value="0">Select Subject</option>
                            @if(!empty($sub_std_map))
        @foreach($sub_std_map as $key => $val)
        <option value="{{ $val->id }}">{{ $val->display_name }}</option>
                                @endforeach
        @endif
        </select>
    </div>
</div>`;

        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Secondary Subject</label><input type="text" name="secondary_subject[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Percentage</label><input type="text" name="percentage[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">CPI</label><input type="text" name="cpi[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">CGPA</label><input type="text" name="cgpa[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Remarks</label><input type="text" name="remarks[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-1 mt-3"><a href="javascript:void(0);" onclick="removeNewRowWithChain();" class="d-inline btn btn-danger"><i class="mdi mdi-minus"></i></a></div></div>';

        $('.addButtonCheckboxEducation:last').after(htmlcontent);
    }

    function addNewRowWithExperience1() {
        data_new = 1;
        var htmlcontent = '';
        htmlcontent += '<div class="clearfix"></div><div class="addButtonCheckboxExperience" style="display: flex; margin-right: -15px; margin-left: -15px; flex-wrap: wrap;"><input type="hidden" name="experience_detail_id[]" value="0">';

        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Teching Type</label><input type="text" name="teching_type[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Teching Type</label> <select name="teching_type[]" id="teching_type" class="form-control mb-0"  data-new="1"><option value="">N/A</option><option value="1">Teaching</option><option value="2">Non-Teaching</option></select></div></div>';

        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Institutional Name</label><input type="text" name="institutional_name[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Designation Name</label><input type="text" name="designation_name[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Exp. Type</label> <select name="experience_type[]" id="experience_type" class="form-control mb-0" data-new="1"><option value="">N/A</option><option value="School Exp.">School Exp.</option><option value="Diploma Exp.">Diploma Exp.</option><option value="Degree Exp."></option><option value="Industrial Exp.">Industrial Exp.</option></select></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Joining Date</label><input type="text" class="form-control mydatepicker" name="joining_date[]" placeholder="dd/mm/yyyy" value="" autocomplete="off" data-new="1"/><span class="input-group-addon"><i class="icon-calender"></i></span></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Leaving Date</label><input type="text" class="form-control mydatepicker" name="leaving_date[]" placeholder="dd/mm/yyyy" value="" autocomplete="off" data-new="1"/><span class="input-group-addon"><i class="icon-calender"></i></span></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Experience</label><input type="text" name="experience[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Remarks</label><input type="text" name="remarks[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-1 mt-3"><a href="javascript:void(0);" onclick="removeNewRowWithChainExperience();" class="d-inline btn btn-danger"><i class="mdi mdi-minus"></i></a></div></div>';

        $('.addButtonCheckboxExperience:last').after(htmlcontent);
    }


    function addNewRowWithTraining() {
        data_new = 1;
        var htmlcontent = '';
        htmlcontent += '<div class="clearfix"></div><div class="addButtonCheckboxTranining" style="display: flex; margin-right: -15px; margin-left: -15px; flex-wrap: wrap;"><input type="hidden" name="training_detail_id[]" value="0">';

        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Training Name</label><input type="text" name="training_name[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Training Subject</label><input type="text" name="training_subject[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Training Place</label><input type="text" name="training_place[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Start Date</label><input type="date" name="start_date[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">End Date</label><input type="text" name="end_date[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Days</label><input type="number" name="days[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Remarks</label><input type="text" name="remarks[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-1 mt-3"><a href="javascript:void(0);" onclick="removeNewRowWithTraining();" class="d-inline btn btn-danger"><i class="mdi mdi-minus"></i></a></div></div>';

        $('.addButtonCheckboxTranining:last').after(htmlcontent);
    }


    function addNewRowWithProfessional() {
        data_new = 1;
        var htmlcontent = '';
        htmlcontent += '<div class="clearfix"></div><div class="addButtonCheckboxProfessional" style="display: flex; margin-right: -15px; margin-left: -15px; flex-wrap: wrap;"><input type="hidden" name="training_detail_id[]" value="0">';

        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Designation</label><input type="text" name="designation[]" value="" class="form-control mb-0"/></div></div>';
        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Appointment Type</label><input type="text" name="appointment_type[]" value="" class="form-control mb-0"/></div></div>';
        var appTypeOptions = @json($appType);
         htmlcontent += '<div class="col-md-2 my-2">';
htmlcontent += '<div class="form-group mb-0">';
htmlcontent += '<label for="control-label">Appointment Type</label>';
htmlcontent += '<select name="appointment_type[]" class="form-control mb-0" data-new="1">';
htmlcontent += '<option value="">N/A</option>'; // Default option

// Loop through appTypeOptions to add options dynamically
$.each(appTypeOptions, function(key, value) {
    htmlcontent += '<option value="' + key + '">' + value + '</option>';
});

htmlcontent += '</select>';
htmlcontent += '</div></div>';


        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Doctorate Degree</label><input type="text" name="doctorate_degree[]" value="" class="form-control mb-0"/></div></div>';

        var doctorate = @json($doctorate);
         htmlcontent += '<div class="col-md-2 my-2">';
htmlcontent += '<div class="form-group mb-0">';
htmlcontent += '<label for="control-label">Doctorate Degree</label>';
htmlcontent += '<select name="doctorate_degree[]" class="form-control mb-0" data-new="1">';
htmlcontent += '<option value="">N/A</option>'; // Default option

// Loop through appTypeOptions to add options dynamically
$.each(doctorate, function(key, value) {
    htmlcontent += '<option value="' + key + '">' + value + '</option>';
});

htmlcontent += '</select>';
htmlcontent += '</div></div>';

        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Doctorate Degree Percentage</label><input type="text" name="doctorate_degree_percentage[]" value="" class="form-control mb-0"/></div></div>';
        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">PG Degree</label><input type="text" name="pg_degree[]" value="" class="form-control mb-0"/></div></div>';
        var pgDegree = @json($pgDegree);
         htmlcontent += '<div class="col-md-2 my-2">';
htmlcontent += '<div class="form-group mb-0">';
htmlcontent += '<label for="control-label">PG Degree</label>';
htmlcontent += '<select name="pg_degree[]" class="form-control mb-0" data-new="1">';
htmlcontent += '<option value="">N/A</option>'; // Default option

// Loop through appTypeOptions to add options dynamically
$.each(pgDegree, function(key, value) {
    htmlcontent += '<option value="' + key + '">' + value + '</option>';
});

htmlcontent += '</select>';
htmlcontent += '</div></div>';

        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">PG Degree Percentage</label><input type="text" name="pg_degree_percentage[]" value="" class="form-control mb-0"/></div></div>';
        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">UG Degree</label><input type="text" name="ug_degree[]" value="" class="form-control mb-0"/></div></div>';

        var ugDegree = @json($ugDegree);
         htmlcontent += '<div class="col-md-2 my-2">';
htmlcontent += '<div class="form-group mb-0">';
htmlcontent += '<label for="control-label">UG Degree</label>';
htmlcontent += '<select name="ug_degree[]" class="form-control mb-0" data-new="1">';
htmlcontent += '<option value="">N/A</option>'; // Default option

// Loop through appTypeOptions to add options dynamically
$.each(ugDegree, function(key, value) {
    htmlcontent += '<option value="' + key + '">' + value + '</option>';
});

htmlcontent += '</select>';
htmlcontent += '</div></div>';

        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">UG Degree Percentage</label><input type="text" name="ug_degree_percentage[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Other Qualification</label><input type="text" name="other_qualification[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Other Qualification Percentage</label><input type="text" name="other_qualification_percentage[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Specification</label><input type="text" name="specification[]" value="" class="form-control mb-0"/></div></div>';
        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">National Publication</label><input type="text" name="national_publication[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2">';
htmlcontent += '<div class="form-group mb-0">';
htmlcontent += '<label for="control-label">National Publication</label>';
htmlcontent += '<select name="national_publication[]" class="form-control mb-0" data-new="1">';
htmlcontent += '<option value="">N/A</option>'; // Default option

// Generate options from 1 to 50
for (var i = 1; i <= 50; i++) {
    htmlcontent += '<option value="' + i + '">' + i + '</option>';
}

htmlcontent += '</select>';
htmlcontent += '</div></div>';

        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">International Publication</label><input type="text" name="international_publication[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2">';
htmlcontent += '<div class="form-group mb-0">';
htmlcontent += '<label for="control-label">International Publication</label>';
htmlcontent += '<select name="international_publication[]" class="form-control mb-0" data-new="1">';
htmlcontent += '<option value="">N/A</option>'; // Default option

// Generate options from 1 to 50
for (var i = 1; i <= 50; i++) {
    htmlcontent += '<option value="' + i + '">' + i + '</option>';
}

htmlcontent += '</select>';
htmlcontent += '</div></div>';

        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">No of Books Published</label><input type="text" name="no_of_books_published[]" value="" class="form-control mb-0"/></div></div>';
htmlcontent += '<div class="col-md-2 my-2">';
htmlcontent += '<div class="form-group mb-0">';
htmlcontent += '<label for="control-label">No of Books Published</label>';
htmlcontent += '<select name="no_of_books_published[]" class="form-control mb-0" data-new="1">';
htmlcontent += '<option value="">N/A</option>'; // Default option

// Generate options from 1 to 50
for (var i = 1; i <= 50; i++) {
    htmlcontent += '<option value="' + i + '">' + i + '</option>';
}

htmlcontent += '</select>';
htmlcontent += '</div></div>';

        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">No of Patents</label><input type="text" name="no_of_patents[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2">';
htmlcontent += '<div class="form-group mb-0">';
htmlcontent += '<label for="control-label">No of Patents</label>';
htmlcontent += '<select name="no_of_patents[]" class="form-control mb-0" data-new="1">';
htmlcontent += '<option value="">N/A</option>'; // Default option

// Generate options from 1 to 50
for (var i = 1; i <= 50; i++) {
    htmlcontent += '<option value="' + i + '">' + i + '</option>';
}

htmlcontent += '</select>';
htmlcontent += '</div></div>';

        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Teaching Experience</label><input type="text" name="teaching_experience[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Total Work Experience</label><input type="text" name="total_work_experience[]" value="" class="form-control mb-0"/></div></div>';
        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Research Experience</label><input type="text" name="research_experience[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2">';
htmlcontent += '<div class="form-group mb-0">';
htmlcontent += '<label for="control-label">Research Experience</label>';
htmlcontent += '<select name="research_experience[]" class="form-control mb-0" data-new="1">';
htmlcontent += '<option value="">N/A</option>'; // Default option

// Generate options from 1 to 50
for (var i = 1; i <= 50; i++) {
    htmlcontent += '<option value="' + i + '">' + i + '</option>';
}

htmlcontent += '</select>';
htmlcontent += '</div></div>';

        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">No of Projects Guided</label><input type="text" name="no_of_projects_guided[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2">';
htmlcontent += '<div class="form-group mb-0">';
htmlcontent += '<label for="control-label">No of Projects Guided</label>';
htmlcontent += '<select name="no_of_projects_guided[]" class="form-control mb-0" data-new="1">';
htmlcontent += '<option value="">N/A</option>'; // Default option

// Generate options from 1 to 50
for (var i = 1; i <= 50; i++) {
    htmlcontent += '<option value="' + i + '">' + i + '</option>';
}

htmlcontent += '</select>';
htmlcontent += '</div></div>';

        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">No of Doctorate Students Guided</label><input type="text" name="no_of_doctorate_students_guided[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2">';
htmlcontent += '<div class="form-group mb-0">';
htmlcontent += '<label for="control-label">No of Doctorate Students Guided</label>';
htmlcontent += '<select name="no_of_doctorate_students_guided[]" class="form-control mb-0" data-new="1">';
htmlcontent += '<option value="">N/A</option>'; // Default option

// Generate options from 1 to 50
for (var i = 1; i <= 50; i++) {
    htmlcontent += '<option value="' + i + '">' + i + '</option>';
}

htmlcontent += '</select>';
htmlcontent += '</div></div>';

        htmlcontent += '<div class="col-md-1 mt-3"><a href="javascript:void(0);" onclick="removeNewRowWithProfessional();" class="d-inline btn btn-danger"><i class="mdi mdi-minus"></i></a></div></div>';

        $('.addButtonCheckboxProfessional:last').after(htmlcontent);
    }


    function addNewRowWithSalary() {
        data_new = 1;
        var htmlcontent = '';
        htmlcontent += '<div class="clearfix"></div><div class="addButtonCheckboxSalary" style="display: flex; margin-right: -15px; margin-left: -15px; flex-wrap: wrap;"><input type="hidden" name="salary_detail_id[]" value="0">';

        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Pay Scale</label><input type="text" name="pay_scale[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Pay Scale</label><select name="pay_scale[]" value="" class="form-control mb-0"><option value="">N/A</option><option value="2">1-10</option><option value="1">10-15</option></select></div></div>';

        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Increment Date Type</label><input type="date" name="increment_date[]" value="" class="form-control mb-0"/></div></div>';
        // htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Salary Mode</label><input type="text" name="salary_mode[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Salary Mode</label><select name="salary_mode[]" value="" class="form-control mb-0"><option value="">N/A</option><option value="1">Cash</option><option value="2">Cheque</option><option value="3">DD</option><option value="4">Online Transfer</option></select></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Basic</label><input type="text" name="basic[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Grade Pay</label><input type="text" name="grade_pay[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Basic Pay</label><input type="text" name="basic_pay[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">DA</label><input type="text" name="da[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">DA Percentage</label><input type="text" name="da_percentage[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">CLA</label><input type="text" name="cla[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">HRA</label><input type="text" name="hra[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">HRA Percentage</label><input type="text" name="hra_percentage[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Vehicle Allowances</label><input type="text" name="vehicle_allowances[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Medical Allowances</label><input type="text" name="medical_allowances[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Other Allowances</label><input type="text" name="other_allowances[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Gross Salary</label><input type="text" name="gross_salary[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Bank Account Number</label><input type="text" name="bank_account_number[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Bank Name</label><input type="text" name="bank_name[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Bank IFSCcode</label><input type="text" name="bank_ifsc_code[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Bank Branch</label><input type="text" name="bank_branch[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">PF Number</label><input type="text" name="pf_number[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-1 mt-3"><a href="javascript:void(0);" onclick="removeNewRowWithSalary();" class="d-inline btn btn-danger"><i class="mdi mdi-minus"></i></a></div></div>';

        $('.addButtonCheckboxSalary:last').after(htmlcontent);
    }


    function addNewRowWithDocument() {
        data_new = 1;
        var htmlcontent = '';
        htmlcontent += '<div class="clearfix"></div><div class="addButtonCheckboxDocument" style="display: flex; margin-right: -15px; margin-left: -15px; flex-wrap: wrap;"><input type="hidden" name="document_detail_id[]" value="0">';

        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">Document Title</label><input type="text" name="document_title[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-2 my-2"><div class="form-group mb-0"><label for="control-label">File</label><input type="text" name="file[]" value="" class="form-control mb-0"/></div></div>';
        htmlcontent += '<div class="col-md-1 mt-3"><a href="javascript:void(0);" onclick="removeNewRowWithDocument();" class="d-inline btn btn-danger"><i class="mdi mdi-minus"></i></a></div></div>';

        $('.addButtonCheckboxDocument:last').after(htmlcontent);
    }

    function removeNewRowWithChain() {
        $(".addButtonCheckboxEducation:last").remove();
    }

    function removeNewRowWithDocument() {
        $(".addButtonCheckboxDocument:last").remove();
    }

    function removeNewRowWithSalary() {
        $(".addButtonCheckboxSalary:last").remove();
    }

    function removeNewRowWithProfessional() {
        $(".addButtonCheckboxProfessional:last").remove();
    }

    function removeNewRowWithChainExperience() {
        $(".addButtonCheckboxExperience:last").remove();
    }

    function removeNewRowWithTraining() {
        $(".addButtonCheckboxTranining:last").remove();
    }


    function remove(id) {
        $(".past_education_id_" + id).remove();
    }


    function removeDocument(id) {
        $(".document_detail_id_" + id).remove();
    }

    function removeExperience(id) {
        $(".experience_detail_id_" + id).remove();
    }

    function removeProfessional(id) {
        $(".professional_detail_id_" + id).remove();
    }

    function removeSalary(id) {
        $(".salary_detail_id_" + id).remove();
    }

    function removeTraining(id) {
        $(".tranining_detail_id_" + id).remove();
    }

    (function () {
        [].slice.call(document.querySelectorAll('.sttabs')).forEach(function (el) {
            new CBPFWTabs(el);
        });
    })();
</script>
<script src="../../../plugins/bower_components/dropify/dist/js/dropify.min.js"></script>
<script>
    $(document).ready(function () {
        var val1 = $.trim($("#user_profile_id").find("option:selected").text());

        if (val1 == 'Lecturer' || val1 == 'LECTURER') {
            $("#total_lecture_div").css("display", "block");
        } else {
            $("#total_lecture_div").css("display", "none");
        }

        // Basic
        $('.dropify').dropify();
        // Translated
        $('.dropify-fr').dropify({
            messages: {
                default: 'Glissez-déposez un fichier ici ou cliquez',
                replace: 'Glissez-déposez un fichier ou cliquez pour remplacer',
                remove: 'Supprimer',
                error: 'Désolé, le fichier trop volumineux'
            }
        });
        // Used events
        var drEvent = $('#input-file-events').dropify();
        drEvent.on('dropify.beforeClear', function (event, element) {
            return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
        });
        drEvent.on('dropify.afterClear', function (event, element) {
            alert('File deleted');
        });
        drEvent.on('dropify.errors', function (event, element) {
            console.log('Has Errors');
        });
        var drDestroy = $('#input-file-to-destroy').dropify();
        drDestroy = drDestroy.data('dropify')
        $('#toggleDropify').on('click', function (e) {
            e.preventDefault();
            if (drDestroy.isDropified()) {
                drDestroy.destroy();
            } else {
                drDestroy.init();
            }
        })
    });

</script>
<script>
    $("#user_profile_id").on("change", function (event) {
        var val1 = $.trim($("#user_profile_id").find("option:selected").text());

        if (val1 == 'Lecturer' || val1 == 'LECTURER') {
            $("#total_lecture_div").css("display", "block");
        } else {
            $("#total_lecture_div").css("display", "none");
        }
    });

    function getUsername() {
        var first_name = document.getElementById("first_name").value;
        var last_name = document.getElementById("last_name").value;
        var username = first_name.toLowerCase() + "_" + last_name.toLowerCase();
        document.getElementById("user_name").value = username;
    }

    // added on 07-05-2025 
    $(document).on('click', '.addRow', function () {
        var tableId = $(this).attr('data-tableId');
        // alert(tableId);
        let $lastRow = $('#'+tableId+' tbody tr:last');
        let $clonedRow = $lastRow.clone();

        // Increment data-new attributes
        $clonedRow.find('input, select').each(function () {
            let currentVal = parseInt($(this).attr('data-new')) || 1;
            $(this).attr('data-new', currentVal + 1).val('');
        });

        // Show remove button on all rows except the first one
        $clonedRow.find('.removeRow').removeClass('d-none');
        $clonedRow.find('.addRow').addClass('d-none');

        $('#'+tableId+' tbody').append($clonedRow);
    });

    $(document).on('click', '.removeRow', function () {
        $(this).closest('tr').remove();
    });

    function validateDates(leavingInput) {
        var $row = $(leavingInput).closest('tr');
        var joiningDate = $row.find('input[name="joining_date[]"]').val();
        var leavingDate = $(leavingInput).val();

        if (!joiningDate) {
            alert('Please select the joining date first.');
            $(leavingInput).val('');
            return;
        }

        var start = parseDMY(joiningDate);
        var end = parseDMY(leavingDate);

        if (start > end) {
            alert('Leaving date cannot be before joining date.');
            $(leavingInput).val('');
            $row.find('input[name="experience[]"]').val('');
            return;
        }

        var years = end.getFullYear() - start.getFullYear();
        var months = end.getMonth() - start.getMonth();
        var days = end.getDate() - start.getDate();

        if (days < 0) {
            months--;
            days += new Date(start.getFullYear(), start.getMonth() + 1, 0).getDate();
        }
        if (months < 0) {
            years--;
            months += 12;
        }

        var experience = years + '.' + months; // e.g. 0.0, 0.1, 1.5 etc.
        $row.find('input[name="experience[]"]').val(experience);
    }

    function validateDays(endDate){
        var $row = $(endDate).closest('tr');
        var joiningDate = $row.find('input[name="start_date[]"]').val();
        var leavingDate = $(endDate).val();

        if (!joiningDate) {
            alert('Please select the start date first.');
            $(endDate).val(''); // Optional: reset the leaving date
            return;
        }

        // Calculate experience in years
        var start = new Date(joiningDate);
        var end = new Date(leavingDate);

        if (start > end) {
            alert('start date cannot be before start date.');
            $(endDate).val('');
            $row.find('input[name="days[]"]').val('');
            return;
        }

        var timeDiff = Math.abs(end.getTime() - start.getTime());
        var diffDays = (Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1); // Calculate difference in days

        $row.find('input[name="days[]"]').val(diffDays);
    }
    function parseDMY(dateStr) {
        // Expecting DD-MM-YYYY
        var parts = dateStr.split('-');
        return new Date(parts[2], parts[1] - 1, parts[0]); // new Date(year, monthIndex, day)
    }

    function deleteData(delete_type,table_name,dataId){
        $.ajax({
            url : '/user/delete_data/'+dataId,
            type: 'GET',
            data : {delete_type:delete_type,table_name:table_name},
            success: function(response) {
                // Don't parse if response is already an object
                var result = response;

                if (result.status == 1) {
                    alert(result.message || "Success");
                    location.reload();
                } else if (result.status == 0) {
                    alert(result.message || "Something went wrong.");
                }
            }

        })
    }
    // end on 07-05-2025 
</script>

@include('includes.footer')
