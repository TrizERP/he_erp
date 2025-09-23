{{-- @include('includes.headcss') @include('includes.header') @include('includes.sideNavigation') --}}
@extends('layout')
@section('container')
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="card">
                @if ($message = Session::get('success'))
                    <div class="alert alert-success alert-block">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <strong>{{ $message }}</strong>
                    </div>
                @endif
                <div class="col-lg-12 col-sm-12 col-xs-12">

                    <form action="{{ route('marks_entry.create') }}" enctype="multipart/form-data" method="post">
                        {{ method_field('GET') }}
                        {{ csrf_field() }}

                        <div class="row">
                            {{ App\Helpers\TermDD($data['term_id']) }}

                            {{ App\Helpers\SearchChain('4', 'required', 'grade,std,div', $data['grade'], $data['standard'], $data['division']) }}
                            <div class="col-md-4 form-group">
                                <label for="title">Select Subject:</label>
                                <select name="subject" id="subject" class="form-control" required>
                                    <option value="">Select</option>
                                    @foreach ($data['subject_dd'] as $id_dd => $arr_dd)
                                        <option value={{ $id_dd }} @if ($data['subject'] == $id_dd) selected @endif>
                                            {{ $arr_dd }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Exam Master Selection added on 22-04-2025 --}}
                            <div class="col-md-4 form-group">
                                <label for="title">Select Exam Master:</label>
                                <select name="exam_master" id="exam_master" class="form-control" required>
                                    <option value="">Select</option>

                                </select>
                            </div>


                            <div class="col-md-4 form-group">
                                <label for="title">Select Exam:</label>
                                <select name="exam" id="exam" class="form-control">
                                    <option value="">Select</option>
                                    @foreach ($data['exam_dd'] as $id_dd => $arr_dd)
                                        <option value={{ $id_dd }} @if ($data['exam'] == $id_dd) selected @endif>
                                            {{ $arr_dd }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 form-group">
                                <center>
                                    <input type="submit" name="submit" value="Search" class="btn btn-success">
                                </center>
                            </div>
                        </div>

                    </form>
                </div>
                @php
                    $users = ['Admin'];
                    $message = '';
                    if (
                        isset($data['approve_status']) &&
                        $data['approve_status']->status == 1 &&
                        isset($data['approved_user'])
                    ) {
                        $message =
                            'Approved By ' .
                            $data['approved_user']->first_name .
                            ' ' .
                            $data['approved_user']->last_name .
                            ' ';
                    }
                @endphp
                @if (in_array(session()->get('user_profile_name'), $users))
                    <form action="{{ route('approve') }}" enctype="multipart/form-data" method="post"
                        style="margin-top:40px">
                        {{ method_field('POST') }}
                        {{ csrf_field() }}
                        <div class="row mb-2 mt-6">
                            <div class="col-md-6 text-right ">
                                <label for="approve">Approved</label>
                                <input type="checkbox" name="approve" id="approve" value="1"
                                    @if (isset($data['approve_status']) && $data['approve_status']->status == 1) checked @endif>
                            </div>
                            <div class="col-md-6">
                                <input type="hidden" name="term_id" value="{{ $data['term_id'] }}">
                                <input type="hidden" name="standard_id" value="{{ $data['standard'] }}">
                                <input type="hidden" name="division_id" value="{{ $data['division'] }}">
                                <input type="hidden" name="subject_id" value="{{ $data['subject'] }}">
                                <input type="hidden" name="exam_id" value="{{ $data['exam'] }}">

                                <input type="submit" class="btn btn-outline-secondary" name="submit" id="submit"
                                    Value="Approved Marks">
                                <div id="passwordHelpBlock" class="form-text">
                                    {{ $message }}</div>
                            </div>
                        </div>
                    </form>
                @endif

                @if (isset($data['stu_data']))
                    <div class="row mb-2">
                        <div class="col-lg-12 col-sm-12 col-xs-12">
                            <span class="d-block p-2  alert-secondary">Note: Please consider this spelling while adding
                                "AB", "N.A." ,"EX".</span>
                        </div>
                    </div>
                    <form action="{{ route('marks_entry.store') }}" enctype="multipart/form-data" method="post">
                        {{ method_field('POST') }}
                        {{ csrf_field() }}
                        <div class="table-responsive">
                            <table class="table-bordered table" id="myTable">
                                <tr>
                                    <th>Enrollment No</th>
                                    <th>Student Name</th>
                                    @if ($data['exam'] == '')
                                        @foreach ($data['exam_dd'] as $id => $val)
                                            <th>{{ $val }}</th>
                                        @endforeach
                                    @else
                                        <th>{{ isset($data['exam_dd'][$data['exam']]) ? $data['exam_dd'][$data['exam']] : 'Marks' }}
                                        </th>
                                    @endif
                                    {{-- <th style="display: none;">Percentage</th>
                                <th style="display: none;">Grade</th>
                                <th>Remark</th> --}}
                                </tr>
                                @php
                                    $arr = $data['stu_data'];
                                    $disable = '';
                                    if (isset($data['approve_status']) && $data['approve_status']->status == 1) {
                                        $disable = 'disabled';
                                    }
                                @endphp
                                @foreach ($arr as $student_id => $student_data)
                                    <tr>
                                        <td>{{ $student_data['enrollment_no'] }}</td>
                                        <td>{{ $student_data['name'] }}</td>
                                        @if ($data['exam'] == '')
                                            @foreach ($data['exam_dd'] as $exam_id => $exam_name)
                                                @php
                                                    if (
                                                        in_array($student_data[$exam_id]['is_absent'], [
                                                            'AB',
                                                            'N.A.',
                                                            'EX',
                                                        ])
                                                    ) {
                                                        $points = isset($student_data[$exam_id]['is_absent'])
                                                            ? $student_data[$exam_id]['is_absent']
                                                            : 0;
                                                    } else {
                                                        $points = isset($student_data[$exam_id]['points'])
                                                            ? $student_data[$exam_id]['points']
                                                            : 0;
                                                    }
                                                    $outof = isset($student_data[$exam_id]['outof'])
                                                        ? $student_data[$exam_id]['outof']
                                                        : 0;
                                                @endphp
                                                <td>
                                                    <input type="text" class="att"
                                                        name="values[{{ $student_id }}][{{ $exam_id }}][points]"
                                                        style="width: 100px;" value="{{ $points }}"
                                                        onchange="check_input(this, {{ $outof }})"
                                                        {{ $disable }} />
                                                    Out Of
                                                    <label>{{ $outof }}</label>
                                                </td>
                                            @endforeach
                                        @else
                                            @php
                                                $exam_id = $data['exam'];
                                                if (
                                                    in_array($student_data[$exam_id]['is_absent'], ['AB', 'N.A.', 'EX'])
                                                ) {
                                                    $points = isset($student_data[$exam_id]['is_absent'])
                                                        ? $student_data[$exam_id]['is_absent']
                                                        : 0;
                                                } else {
                                                    $points = isset($student_data[$exam_id]['points'])
                                                        ? $student_data[$exam_id]['points']
                                                        : 0;
                                                }
                                                $outof = isset($student_data[$exam_id]['outof'])
                                                    ? $student_data[$exam_id]['outof']
                                                    : 0;
                                            @endphp
                                            <td>
                                                <input type="text" class="att"
                                                    name="values[{{ $student_id }}][{{ $exam_id }}][points]"
                                                    style="width: 100px;" value="{{ $points }}"
                                                    onchange="check_input(this, {{ $outof }})"
                                                    {{ $disable }} />
                                                Out Of
                                                <label>{{ $outof }}</label>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                {{-- @php
                            $arr = $data['stu_data'];
                             $disable  = "";                            
                            if(isset($data['approve_status']) && $data['approve_status']->status ==1){
                                $disable = "disabled";
                            }
                            foreach ($arr as $id=>$col_arr){
                            @endphp

                            <tr>
                            <input type="hidden" class="total_days" value="{{ $col_arr['outof'] }}" />
                            <input type="hidden" name="values[{{ $col_arr['student_id'] }}][exam_id]" value="{{$data['exam']}}" />
                           
                            <td>{{$col_arr['enrollment_no']}}</td>
                            <td>{{$col_arr['name']}}</td>
                            <td> 
                                <input type="text" class="att" name="values[{{ $col_arr['student_id'] }}][points]" style="width: 100px;" value="{{ $col_arr['points'] }}" onchange="check_input(this,{{$col_arr['outof']}})" {{$disable}} />
                                Out Of 
                                <lable>{{$col_arr['outof']}}</lable>
                             </td>
                            <td style="display: none;"><label class="at_per">{{ $col_arr['per'] }}%</label> <input type="hidden" class="at_per_val" name="values[{{ $col_arr['student_id'] }}][per]" readonly="readonly" style="width: 70px;"  value="{{ $col_arr['per'] }}%" /></td>

                            <td style="display: none;"><label class="at_grd">{{ $col_arr['grade'] }}</label> <input type="hidden" class="at_grd_val" name="values[{{ $col_arr['student_id'] }}][grade]" readonly="readonly" style="width: 70px;"  value="{{ $col_arr['grade'] }}" /></td>
                            <td>
                                <textarea name="values[{{ $col_arr['student_id'] }}][comment]" rows="2" cols="20">{{ $col_arr['comment'] }}</textarea>
                            </td>
                            </tr>
                            @php
                            }
                            @endphp --}}
                            </table>
                        </div>
                        @if (isset($data['approve_status']->status) && $data['approve_status']->status == 1)
                        @else
                            <div class="col-md-12 form-group mt-4">
                                <center>
                                    <input type="submit" name="submit" value="Save" class="btn btn-success">
                                </center>
                            </div>
                        @endif
                    </form>
                @else
                    No Student Found.
                @endif
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
        // Set required fields
        $("#grade, #standard, #division, #subject").prop('required', true);

        $('#term').change(function() {
            $('#grade').val('');
            resetDropdowns(['#standard', '#exam_master', '#division', '#subject', '#exam']);
        });
        // Reset subject and exam on grade or standard change
        $('#grade, #standard').change(function() {
            resetDropdowns(['#subject', '#exam']);
        });

        // Fetch subjects based on division
        $('#division').on('change', function() {
            fetchDropdownData('/api/get-subject-list', {
                standard_id: $("#standard").val(),
                division_id: $("#division").val()
            }, '#subject');
        });

        // Fetch exam master based on subject and term
        $(document).ready(function() {
            initializeExamMasterDropdown();
        });

        function initializeExamMasterDropdown() {
            @if (isset($data['exam_master']) && $data['exam_master'] != null)
                fetchDropdownData('/api/get-exam-master-list', {
                    standard_id: $("#standard").val(),
                    term_id: $("#term").val()
                }, '#exam_master', "{{ $data['exam_master'] }}");
            @endif

            @if (isset($data['exam']) && $data['exam'] != null && isset($data['exam_master']) && $data['exam_master'] != null)
                fetchDropdownData('/api/get-exam-list', {
                    standard_id: $("#standard").val(),
                    subject_id: $("#subject").val(),
                    term_id: $("#term").val(),
                    exam_id: {{ $data['exam_master'] }},
                    'searchType': 'co',
                }, '#exam', "{{ $data['exam'] }}");
            @endif
        }

        $('#subject').on('change', function() {
            fetchDropdownData('/api/get-exam-master-list', {
                standard_id: $("#standard").val(),
                term_id: $("#term").val()
            }, '#exam_master');
            $('#exam').empty().append('<option value="">Select</option>');
        });

        // Fetch exams based on exam master
        $('#exam_master').on('change', function() {
            fetchDropdownData('/api/get-exam-list', {
                standard_id: $("#standard").val(),
                subject_id: $("#subject").val(),
                term_id: $("#term").val(),
                exam_id: $("#exam_master").val(),
                'searchType': 'co',
            }, '#exam');
        });

        // Helper function to reset dropdowns
        function resetDropdowns(selectors) {
            selectors.forEach(selector => {
                $(selector).empty().append('<option value="">Select</option>');
            });
        }

        // Helper function to fetch dropdown data
        function fetchDropdownData(url, params, target, salVal = '') {
            if (Object.values(params).every(val => val)) {
                $.ajax({
                    type: "GET",
                    url: url + '?' + $.param(params),
                    success: function(res) {
                        if (res) {
                            $(target).empty().append('<option value="">Select</option>');
                            $.each(res, function(key, value) {
                                if (salVal && key == salVal) {
                                    $(target).append('<option value="' + key + '" selected>' + value +
                                        '</option>');
                                } else {
                                    $(target).append('<option value="' + key + '">' + value +
                                        '</option>');
                                }
                            });
                        } else {
                            $(target).empty().append('<option value="">Select</option>');
                        }
                    }
                });
            } else {
                $(target).empty().append('<option value="">Select</option>');
            }
        }
        const gradeData = @json($data['grd_data']);

        function updateGradeAndPercentage(row) {
            const marks = parseFloat(row.find('.att').val());
            const totalMarks = parseFloat(row.find('.total_days').val());

            if (isNaN(marks) || marks > 500) {
                alert("Marks should not exceed 500 or be invalid.");
                row.find('.att').val(0);
                row.find('.at_grd_val, .at_per_val').val("-");
                row.find(".at_grd, .at_per").text("-");
                return;
            }

            const percentage = ((marks / totalMarks) * 100).toFixed(2);
            row.find('.at_per_val').val(`${percentage}%`);
            row.find(".at_per").text(`${percentage}%`);

            let grade = "-";
            $.each(gradeData, (key, range) => {
                if (range.includes(Math.round(percentage))) {
                    grade = key;
                }
            });

            row.find('.at_grd_val').val(grade);
            row.find(".at_grd").text(grade);
        }

        function validateInput(input, maxMarks) {
            const values = input.value.trim().split(/\s+/);
            let total = 0;
            let isValid = true;

            values.forEach(value => {
                if (!["AB", "N.A.", "EX"].includes(value) && isNaN(parseInt(value))) {
                    isValid = false;
                } else if (!isNaN(parseInt(value))) {
                    total += parseInt(value);
                }
            });

            if (total > maxMarks) {
                alert(`Total value cannot exceed ${maxMarks}.`);
                input.value = 0;
            }

            if (!isValid) {
                alert("Enter valid values: digits or 'AB', 'N.A.', 'EX'.");
                input.value = 0;
            }
        }

        $('.att').on('change blur', function() {
            const row = $(this).closest('tr');
            updateGradeAndPercentage(row);
        });

        function check_input(inputElement, maxMarks) {
            validateInput(inputElement, maxMarks);
        }
    </script>
    @include('includes.footer')
@endsection
