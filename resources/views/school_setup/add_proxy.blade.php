@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<style>
    .title {
        font-weight: 200;
    }
</style>
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Add Proxy Management</h4>
            </div>
        </div>
        <div class="card">
            @if ($message = Session::get('data'))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $message['message'] }}</strong>
                </div>
            @endif
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <form action="{{ route('ajax_getproxyperiod') }}" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>From Date</label>
                                <div class="input-daterange input-group" id="date-range">
                                    <input value="@if (isset($data['from_date'])) {{ $data['from_date'] }} @endif"
                                        type="text" required class="form-control mydatepicker"
                                        placeholder="YYYY/MM/DD" name="from_date" id="from_date" autocomplete="off">
                                    <span class="input-group-addon"><i class="icon-calender"></i></span>
                                </div>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>To Date</label>
                                <div class="input-daterange input-group" id="date-range">
                                    <input value="@if (isset($data['to_date'])) {{ $data['to_date'] }} @endif"
                                        type="text" required class="form-control mydatepicker"
                                        placeholder="YYYY/MM/DD" name="to_date" id="to_date" autocomplete="off">
                                    <span class="input-group-addon"><i class="icon-calender"></i></span>
                                </div>
                            </div>
                            @php
                                $dep_id = $emp_id = '';
                                if (isset($data['department_id'])) {
                                    $dep_id = $data['department_id'];
                                }

                                if (isset($data['selected_emp'])) {
                                    $emp_id = $data['selected_emp'];
                                }
                            @endphp

                            {!! App\Helpers\HrmsDepartments('', '', $dep_id, '', $emp_id, '') !!}
                            <div class="col-md-12 form-group">
                                <center>
                                    <input type="submit" name="submit" value="Submit" class="btn btn-success"
                                        onclick="return validate_dates();">
                                </center>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <div class="alert alert-danger alert-dismissable" id='showerr' style="display:none;">
                        <div id='err'></div>
                    </div>
                </div>
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        <form action="{{ route('proxy_master.store') }}" name="proxy" id="proxy"
                            enctype="multipart/form-data" method="post">
                            @if (isset($data['proxydata']))
                                {{ method_field('POST') }}
                            @endif
                            @csrf
                            @if (isset($data['proxydata']))
                                <table id="proxy_list" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Sr.No.</th>
                                            <th>Date</th>
                                            <th>Week Day</th>
                                            <th>{{ App\Helpers\get_string('standard', 'request') }}</th>
                                            <th>{{ App\Helpers\get_string('division', 'request') }}</th>
                                            <th>Batch</th>
                                            <th>Period</th>
                                            <th>Subject</th>
                                            <th>Branch</th>
                                            <th>Proxy Lecturer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($data['proxydata'] as $key => $val)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="period"
                                                        name="proxy_id['{{ $val['date'] }}/{{ $val['timetable_id'] }}']"
                                                        id="proxy_id['{{ $val['date'] }}/{{ $val['timetable_id'] }}']">
                                                </td>
                                                <td>{{ $i++ }}</td>
                                                <td>{{ $val['date'] }}</td>
                                                <td>{{ $val['week_day'] }}</td>
                                                <td>{{ $val['standard_name'] }}</td>
                                                <td>{{ $val['division_name'] }}</td>
                                                <td>
                                                    @if (isset($val['batch_name']))
                                                        {{ $val['batch_name'] }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $val['period_name'] }}</td>
                                                <td>{{ $val['subject_name'] }}</td>
                                                @php
                                                    $check = DB::table('proxy_master')
                                                        ->whereRaw(
                                                            'sub_institute_id=' .
                                                                session()->get('sub_institute_id') .
                                                                ' and syear=' .
                                                                session()->get('syear') .
                                                                ' and teacher_id=' .
                                                                $data['teacher'] .
                                                                ' and period_id=' .
                                                                $val['period_id'] .
                                                                ' and subject_id=' .
                                                                $val['subject_id'] .
                                                                '',
                                                        )
                                                        ->whereBetween('proxy_date', [
                                                            $data['from_date'],
                                                            $data['to_date'],
                                                        ])
                                                        ->get()
                                                        ->toArray();
                                                @endphp
                                                 <td style="width:200px;">
                                                    <select name="table_branch1" class="form-control table_branch1" onchange="getEmployeeList(this)">
                                                        <option value="">Select any One</option>
                                                        @if(isset($data['departmentLists']))
                                                        @foreach ($data['departmentLists'] as $key=>$dept )
                                                        <option value="{{$dept->id}}" @if(isset($data['department_id']) && $data['department_id']==$dept->id) selected @endif>{{$dept->department}}</option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                </td>
                                                <td style="width:400px;">
                                                    <select class="selectpicker form-control employee_list"
                                                        name="teacher_id['{{ $val['date'] }}/{{ $val['timetable_id'] }}']"
                                                        id="teacher_id['{{ $val['date'] }}/{{ $val['timetable_id'] }}']"
                                                        @if (!empty($check)) style="pointer-events:none" readonly @endif>
                                                        <option value="">--Select Lecturer--</option>
                                                        <!-- Employees will be loaded via AJAX -->

                                                        <!-- ADDED BY RAJESH FOR FREE TEACHER DISPLAY -->
                                                        @if (isset($val['teacher_data']))
                                                                @foreach ($val['teacher_data'] as $key => $val2)
                                                                    @php
                                                                        $isAbsentTeacher =
                                                                            isset($data['teacher']) &&
                                                                            $data['teacher'] == $val2->id;
                                                                        $isAssigned = isset(
                                                                            $data['check_data'][$val['date']][
                                                                                $val['period_id']
                                                                            ][$val2->id],
                                                                        );
                                                                    @endphp

                                                                    @if (!$isAbsentTeacher && !$isAssigned)
                                                                        <option value="{{ $val2->id }}">
                                                                            {{ $val2->teacher_name }}</option>
                                                                    @endif
                                                                @endforeach
                                                        @endif
                                                        <!-- END -->
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @if (count($data['proxydata']) > 0)
                                        <tr align="center">
                                            <td colspan="11">
                                                <center>
                                                    <input onclick="return validate_data();" type="submit"
                                                        name="Save" value="Save" class="btn btn-success">
                                                </center>
                                            </td>
                                        </tr>
                                    @else
                                        <tr align="center">
                                            <td colspan="10">
                                                No Records Found!
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>

@include('includes.footerJs')
<script>
    function validate_data() {
        var err = 0;
        var selected_checkbox = $("input:checkbox[class=period]:checked").length;
        $("input:checkbox[class=period]:checked").each(function() {
            var id = $(this).attr("id");
            tval = id.replace("proxy_id", "teacher_id");
            var tval = document.getElementById(tval).value;;
            if (tval == "" || tval == "undefined") {
                err = 1;
            }
        });
        if (err == 1) {
            $("#showerr").css("display", "block");
            $("#err").html("Please Select Lecturer");
            return false;
        } else if (selected_checkbox <= 0) {
            $("#showerr").css("display", "block");
            $("#err").html("Please Select Atleast One Proxy Lecture");
            return false;
        } else {
            return true;
        }
    }

    $(document).ready(function() {
        // Initialize all selectpickers on page load
        $('.selectpicker').selectpicker();
        
        // Pre-load employee lists for each row if department is selected
        @if(isset($data['department_id']) && $data['department_id'])
            // Load employees for all rows with the selected department
            $('.table_branch').each(function() {
                if($(this).val() == '{{ $data["department_id"] }}') {
                    getEmployeeList($(this), true);
                }
            });
        @endif
    });

    function getEmployeeList(element, isPageLoad = false) {
        let department_id = $(element).val();
        let row = $(element).closest('tr');
        let employee_select = row.find('.employee_list');
        
        if(department_id) {
            $.ajax({
                url: '/departmentwise-emplist',
                type: 'GET',
                data: {
                    department_id: department_id
                },
                success: function(response) {
                    // Store the current value before updating options
                    let currentValue = employee_select.val();
                    
                    // Clear existing options and add default option
                    employee_select.empty().append('<option value="">--Select Lecturer--</option>');
                    
                    // Add new options
                    if(response && response.length > 0) {
                        $.each(response, function(index, employee) {
                            employee_select.append($('<option>', {
                                value: employee.id,
                                text: employee.full_name
                            }));
                        });
                    }
                    
                    // Reinitialize the selectpicker to reflect changes
                    // employee_select.selectpicker('destroy');
                    // employee_select.selectpicker();
                    
                    // // On page load, don't try to restore previous selection
                    // if (!isPageLoad && currentValue) {
                    //     employee_select.selectpicker('val', currentValue);
                    // }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching employee list:', error);
                    employee_select.empty().append('<option value="">--Select Lecturer--</option>');
                    employee_select.selectpicker('destroy');
                    employee_select.selectpicker();
                }
            });
        } else {
            employee_select.empty().append('<option value="">--Select Lecturer--</option>');
            employee_select.selectpicker('destroy');
            employee_select.selectpicker();
        }
    }
</script>
@include('includes.footer')