@extends('layout')
@section('container')
    <div id="page-wrapper">
        <div class="container-fluid">

            <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title">Take Attendance</h4>
                </div>
            </div>
        </div>
        @php
            $grade_id = $standard_id = $division_id = $enrollment_no = $receipt_no = $to_date = '';
            $from_date = now();
            if (isset($data['grade_id'])) {
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            if (isset($data['from_date'])) {
                $from_date = $data['from_date'];
            }

        @endphp

        @if (session()->get('user_profile_name') == 'Lecturer' || session()->get('user_profile_name') == 'LMS Teacher')
            <script>
                $(document).ready(function() {
                    $('#from_date').datepicker({
                        autoclose: true,
                        todayHighlight: true,
                        minDate: new Date() // Set the minimum date to today
                    });
                });
            </script>
        @endif

        <div class="card"> <!--  py-0 -->
            @if ($sessionData = Session::get('data'))
                @if ($sessionData['status_code'] == 1)
                    <div class="alert alert-success alert-block">
                    @else
                        <div class="alert alert-danger alert-block">
                @endif
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $sessionData['message'] }}</strong>
        </div>
        @endif

        <form action="{{ route('students_attendance.create') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group d-flex text-center">
                        <div class="form-check">
                            @php
                                $checked = 'checked';
                                $proxy = $extra = '';
                                $type = isset($data['exampleRadios']) ? $data['exampleRadios'] : '';

                                if (isset($data['exampleRadios'])) {
                                    if ($data['exampleRadios'] == 'Proxy') {
                                        $proxy = 'checked';
                                        $checked = '';
                                    } elseif ($data['exampleRadios'] == '') {
                                        $extra = 'checked';
                                        $checked = '';
                                    }
                                }
                            @endphp

                            <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1"
                                value="Regular" {{ $checked }}>
                            <label class="form-check-label" for="exampleRadios1">Regular</label>
                        </div>
                        @if (isset($data['show']) && $data['show'] == 1)
                            <div class="form-check ml-2">
                                <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2"
                                    value="Proxy" {{ $proxy }}>
                                <label class="form-check-label" for="exampleRadios2">Proxy</label>
                            </div>
                            <div class="form-check ml-2">
                                <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios3"
                                    value="Extra" {{ $extra }}>
                                <label class="form-check-label" for="exampleRadios3">Extra</label>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-2" id="attendanceTypeSelect">
                    <label for="attendance_type">Select Type</label>
                    <select name="attendance_type" id ="attendance_type_select" class="form-control">
                        @php
                            $att_type = ['Lecture', 'Lab', 'Tutorial'];
                        @endphp

                        @foreach ($att_type as $key => $value)
                            <option value="{{ $value }}" @if (isset($data['attendance_type']) && $data['attendance_type'] == $value) selected @endif>
                                {{ $value }}</option>
                        @endforeach
                    </select>
                </div>

            </div>


            <div class="row">
                <div class="col-md-2 form-group">
                    <label>Date</label>
                    <input type="text" id="from_date" name="from_date" value="{{ $from_date }}"
                        class="form-control mydatepicker" autocomplete="off">
                </div>

                {{ App\Helpers\SearchChain('2', 'single', 'grade,std,div', $grade_id, $standard_id, $division_id) }}
                <div class="col-md-2">
                    <input type="hidden" id="subject_name" name="subject_name"
                        @if (isset($data['subject_name'])) value="{{ $data['subject_name'] }}" @endif>
                    <label for="subject">Subject :</label>
                    <select class="form-control" id="subject" name="subject" required>
                        @if (!empty($data['all_subject']))
                            @foreach ($data['all_subject'] as $index => $val)
                                @if (!empty($val))
                                    @foreach ($val as $key => $value)
                                        <option value="{{ $value['subject_id'] . '|||' . $value['period_id'] }}" 
                                                data-type="{{ $value['type'] }}"
                                                data-periodid="{{ $value['period_id'] }}"
                                    data-timetableid="{{ $value['timetable'] }}"
                                    >
                                            {{ $value['subject'] }}
                                        </option>
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    </select>

                </div>

                {{-- <div class="col-md-2" id="lecture_div">
                    <input type="hidden" id="lecture_name" name="lecture_name"
                        @if (isset($data['lecture_name'])) value="{{ $data['lecture_name'] }}" @endif>
                    <input type="hidden" id="batch_id" name="batch_id"
                        @if (isset($data['batch_id'])) value="{{ $data['batch_id'] }}" @endif>

                    <label for="lecture">Lectures :</label>
                    <select class="form-control" id="lecture" name="lecture" required>
                        @if (!empty($data['all_lecture']))
                            @foreach ($data['all_lecture'] as $key => $value)
                                <option value="{{ $value->period_id }}" data-id="{{ $value->timetable_id }}"
                                    data-batchid="{{ $value->batch_ids }}"
                                    @if (isset($data['lecture']) && $data['lecture'] == $value->period_id) selected @endif>{{ $value->period_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div> --}}

                <div class="col-md-2" id="batch_div">
                    <input type="hidden" id="batch_name" name="batch_name"
                        @if (isset($data['batch_name'])) value="{{ $data['batch_name'] }}" @endif>
              
                    <label for="batch">Batch :</label>
                    <select class="form-control" id="batch" name="batch">
                        @if (!empty($data['batchs']['original']))
                            @foreach ($data['batchs']['original'] as $value)
                                <option value="{{ $value['id'] }}" 
                                    data-id="{{ $value['timetable_id'] }}"
                                    data-batchid="{{ $value['period_id'] }}"
                                    @if (isset($data['batch_id']) && $data['batch_id'] == $value['id']) selected @endif>
                                    {{ $value['batch'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

            </div>
            <div class="row">
                <input type="hidden" name="timetable_id" id="timetable_id">
                <input type="hidden" name="period_id" id="period_id">

                <div class="col-md-12 form-group">
                    <center>
                        <input type="submit" name="submit" value="Search" class="btn btn-success">
                    </center>
                </div>
            </div>

            <div>
            </div>
        </form>
    </div>

    @if (isset($data['student_data']))
        @php
            $j = 1;
            if (isset($data['student_data'])) {
                $student_data = $data['student_data'];
            }
        @endphp
        <div class="card">
            <form method="POST" action="{{ route('students_attendance.store') }}">
                @csrf
                @php 
                    $subject_id=explode('|||',$data['subject'] ?? '');
                @endphp
                <input type="hidden" name="subjects_id" value="{{ $subject_id[0] ?? 0 }}">
                <input type="hidden" name="periods_id" value="{{ $subject_id[1] ?? 0 }}">
                <input type="hidden" name="timetables_id" value="{{ $data['timetable_id'] }}">
                <input type="hidden" name="batchs_id" value="{{ $data['batch_id'] }}">
                <input type="hidden" name="att_type" value="{{ $data['exampleRadios'] }}">
                <input type="hidden" name="att_for" value="{{ $data['attendance_type'] }}">

                <div class="table-responsive">
                    <table id="example" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Subject</th>
                                <th>Lecture</th>
                                <th>{{ App\Helpers\get_string('grno', 'request') }}</th>
                                <th>Roll No</th>
                                <th>Last Name</th>
                                <th>{{ App\Helpers\get_string('studentname', 'request') }}</th>
                                <th>Middle Name</th>
                                @if (isset($data['batch_id']) && !empty($data['batchs']))
                                    <th>Batch</th>
                                @endif
                                <th>Present <input id="checkall" name="attendance" onchange="checkAll(this,'Present');"
                                        type="radio"></th>
                                <th>Absent <input id="checkall" name="attendance" onchange="checkAll(this,'Absent');"
                                        type="radio"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($student_data as $key => $value)
                                <tr>
                                    <td> {{ $j++ }} </td>
                                    <td> {{ $data['subject_name'] }} </td>
                                    <td> {{ $data['lecture_name'] }} </td>
                                    <td> {{ $value['enrollment_no'] }} </td>
                                    <td> {{ $value['roll_no'] }} </td>
                                    <td> {{ $value['last_name'] }} </td>
                                    <td> {{ $value['first_name'] }} </td>
                                    <td> {{ $value['middle_name'] }} </td>
                                    @if (isset($data['batch_id']) && !empty($data['batchs']))
                                        <td>{{ $value['batch_title'] }}</td>
                                    @endif
                                    <td> <input type="radio" value="P"
                                            @if (!isset($data['attendance_data'][$value['id']]) || $data['attendance_data'][$value['id']] == 'P') checked @endif class="Present"
                                            name="student[{{ $value['id'] }}]"> </td>
                                    <td> <input type="radio" value="A"
                                            @if (isset($data['attendance_data'][$value['id']]) && $data['attendance_data'][$value['id']] == 'A') checked @endif class="Absent"
                                            name="student[{{ $value['id'] }}]"> </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <center>
                                <input type="hidden" name="date"
                                    @if (isset($from_date)) value="{{ $from_date }}" @endif">
                                <input type="hidden" name="standard_division"
                                    @if (isset($standard_id) && isset($division_id)) value="{{ $standard_id }}||{{ $division_id }}" @endif">
                                <input type="submit" name="submit" value="Submit" class="btn btn-success">
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @endif

    </div>

    </div>
    </div>

    @include('includes.footerJs')

    <script type="text/javascript">
        $(document).ready(function() {
            var type = '{{ $type }}';
            // var all_subject = @json($data['all_subject'] ?? []); 
            // if (all_subject && all_subject.length > 0) {
            //         $('#batch_div').show();
            //     }
            $('#attendanceTypeSelect').hide();
            if (type == "Proxy") {
                $('#attendanceTypeSelect').show();
            }
            // $('#lecture_div').hide();
            $('#batch_div').hide();

            //chek for type
            $('input[name="exampleRadios"]').on('change', function() {
                if ($(this).val() == "Proxy") {
                    $('#attendanceTypeSelect').show();
                } else {
                    $('#attendanceTypeSelect').hide();
                }
            });

            $('#attendance_type_select').on('change', function() {
                var selectedType = $(this).val();
                var lectureType = $('input[name="exampleRadios"]:checked').val(); // <-- fixed

                if (selectedType !== "Lecture" && lectureType === "Proxy") {
                    $('#batch_div').show();
                } else {
                    $('#batch_div').hide();
                }
            });


            // get subject lists 
            $('#division').on('change', function() {
                $("#subject").empty();
                var selectedStandard = $('#standard').val();
                var from_date = $('#from_date').val();
                var selectedDivision = $(this).val();

                if (selectedStandard) {
                    $.ajax({
                        type: "GET",
                        url: "/api/get-subject-list-timetable?standard_id=" + selectedStandard +
                            '&division_id=' + selectedDivision + '&date=' + from_date,
                        success: function(res) {
                            if (res) {
                                $("#subject").empty();
                                $("#subject").append('<option value="">Select</option>');
                                $.each(res, function(key, value) {
                                    $("#subject").append('<option value="' + value
                                        .subject_id + '|||' + value.period_id +
                                        '" data-type="' + value.type +
                                        '" data-periodid="' + value.period_id +
                                        '" data-timetableid="' + value.timetable +
                                        '">' + value.subject + '</option>');
                                });

                            } else {
                                $("#subject").empty();
                            }
                        }
                    });
                } else {
                    $("#subject").empty();
                }

            });

            // get lectures 
            $('#subject').on('change', function() {
                // $('#lecture_div').show();
                $('#lecture').empty();
                $('#subject_name').empty();

                var selectedSubject = $(this).val();
                var selectedOption = $('#subject option:selected');
                var type = selectedOption.data('type');
                var period_id = selectedOption.data('periodid');
                var timetable_id = selectedOption.data('timetableid');
                // alert(timetable_id);

                $('#timetable_id').val(timetable_id);
                // console.log(type+'==='+period_id);
                var selectedOption = $('#subject option:selected');
                var name = selectedOption.text();
                $('#subject_name').val(name);

                var selectedStandard = $('#standard').val();
                var selectedDivision = $('#division').val();
                var selectedDate = $('#from_date').val();

                if (type !== 'Lab' && type!== 'Tutorial') {
                    $('#batch_div').hide();
                } else {
                    $('#batch_div').show();
                }
                // alert(selectedDate);
                if (selectedSubject) {
                    $.ajax({
                        type: "GET",
                        url: "/api/get-batch-list-timetable",
                        data: {
                            subject_id: selectedSubject,
                            standard_id: selectedStandard,
                            division_id: selectedDivision,
                            date: selectedDate,
                            type: type,
                            period_id: period_id
                        },
                        success: function(res) {
                            // console.log(res);

                            if (res.length > 0) {
                                // $("#lecture").empty();
                                $("#batch").empty();
                                // $("#lecture").append('<option value="">Select</option>');
                                $("#batch").append('<option value="">Select</option>');

                                $.each(res, function(key, value) {
                                    if (value.batch != null) {
                                        $("#batch").append('<option value="' +
                                            value.id + '">' + value.batch +
                                            '</option>');

                                        // $("#lecture").append('<option value="' + value
                                        //     .period_id + '" data-id="' + value
                                        //     .timetable_id + '" data-batchid="' +
                                        //     value.bid + '">' + value.period_name +
                                        //     '</option>');

                                        // Create $batch options based on batch_ids and batch_name
                                        // var batch = $('#batch');
                                        // var batchIds = value.batch_ids.split(',');
                                        // var batchNames = value.batch_name.split(',');
                                        // for (var i = 0; i < batchIds.length; i++) {
                                        //     batch.append('<option value="' + batchIds[
                                        //         i] + '">' + batchNames[i] +
                                        //         '</option>');
                                        // }
                                    }


                                });

                            } else {
                                $("#batch").empty();
                            }
                        }
                    });
                }

            });

            // get batch 
            $('#lecture').on('change', function() {
                $('#lecture_name').empty();
                $('#batch_id').empty();

                var selectedOption = $(this).find('option:selected');
                var timetable_id = selectedOption.data('id');
                var batch_id = selectedOption.data('batchid');
                var period_id = selectedOption.val();
                var name = selectedOption.text();

                $('#lecture_name').val(name);
                $('#batch_id').val(batch_id);
                $('#period_id').val(period_id);
                $('#timetable_id').val(timetable_id);

            })

            $('#batch').on('change', function() {
                $('#batch_name').empty();

                var selectedOption = $(this).find('option:selected');
                var name = selectedOption.text();
                var timetable_id = selectedOption.data('id');
                $('#batch_name').val(name);

            })

        });
    </script>
    @include('includes.footer')
@endsection
