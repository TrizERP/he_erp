@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                <h4 class="page-title">Subjectwise Detailed Semester Report</h4>
            </div>
        </div>

        @php

            $grade_id = $standard_id = $division_id = $subject = '';

            if (isset($data['grade_id'])) {
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            if (isset($data['subject'])) {
                $subject = $data['subject'];
            }
            $getInstitutes = session()->get('getInstitutes');
        @endphp

        <div class="card">
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

            <form action="{{ route('show_monthwise_student_attendance_report') }}" enctype="multipart/form-data" method="post">
                @csrf
                <div class="row">
                    {{ App\Helpers\SearchChain('3', 'single', 'grade,std,div', $grade_id, $standard_id, $division_id) }}

                    <div class="col-md-3 form-group">
                        <label for="">Type</label>
                        <select name="lecture_type" id="lecture_type" class="form-control" required>
                            <option value="">-Select Type-</option>
                            @foreach ($data['types'] as $k => $value)
                                <option value="{{ $value }}" @if(isset($data['lecture_type']) && $data['lecture_type']==$value) selected @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="">Subject</label>
                        <select name="subject" id="subject" class="form-control" required>
                            <option value="">-Select Subject-</option>
                        </select>
                    </div>

                    @if (isset($data['batch']) && !empty($data['batchs']))
                        <div class="col-md-3 form-group" id="batch_div">
                            <label>-Select Batch-</label>
                            <select name="batch_sel" class="form-control" id="batch_sel" required="">
                                @foreach ($data['batchs'] as $batch)
                                    <option value="{{ $batch->id }}" @if ($data['batch_id'] == $batch->id) selected @endif>
                                        {{ $batch->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-12 form-group">
                        <center>
                            <input type="submit" name="submit" value="Search" class="btn btn-success">
                        </center>
                    </div>
                </div>
            </form>
        </div>

        @if (isset($data['student_data']))
            @php
                echo App\Helpers\get_school_details($grade_id, $standard_id, $division_id,$subject);
            @endphp

            <div class="card">
                {{-- School header/address --}}
                @php
                    echo App\Helpers\get_school_details($grade_id, $standard_id, $division_id);

                     $getInstitutes = session()->get('getInstitutes');
                     $academicYears = session()->get('academicYears');
                     $syear = session()->get('syear');


            $nextYear = $syear + 1;

                @endphp

                {{-- ✅ Academic Year Label (same font as address) --}}
           
                <center>
                    <span style="font-size: 15px; font-weight: 600; font-family: Arial, Helvetica, sans-serif !important; display:block; margin-top: 15px; margin-bottom: 5px;">
                        Academic Year :{{ $syear }} - {{ $nextYear }}
                    </span>
                </center>
               

                {{-- Report Title --}}
                <h1 style="text-align:center; font-size:20px; margin-top:5px; font-family:inherit; color:black;">
                    Subjectwise Detailed Semester Report
                </h1>

                <div class="table-responsive">
                    <table id="example" class="table display" style="border:none !important">
                        <thead>
                            <tr id="heads">
                                <th>{{ App\Helpers\get_string('grno', 'request') }}</th>
                                <th>{{ App\Helpers\get_string('studentname', 'request') }}</th>
                                @foreach($data['dateArr'] as $key => $date)
                                    <th>{{ \Carbon\Carbon::parse($date)->format('d/m') }}</th>
                                @endforeach
                                <th>Total</th>
                                <th>Present</th>
                                <th>Absent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student_data as $stu)
                                @php
                                    $total = $present = $absent = 0;
                                @endphp
                                <tr>
                                    <td>{{ $stu->enrollment_no }}</td>
                                    <td>{{ $stu->student_name }}</td>
                                    @foreach($data['dateArr'] as $key => $date)
                                        @php
                                            $code = $data['studentArr'][$stu->student_id][$key] ?? '-';
                                            if($code != '-') $total++;
                                            if($code == 'P') $present++;
                                            if($code == 'A') $absent++;
                                        @endphp
                                        <td>{!! $code == 'A' ? '<b>A</b>' : $code !!}</td>
                                    @endforeach
                                    <td>{{ $total }}</td>
                                    <td>{{ $present }}</td>
                                    <td>{{ $absent }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Signature Section --}}
                    <div style="margin-top:60px; padding:20px 0; width:100%; color:black">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; width:100%;">
                            <div style="text-align:left; width:33%;">
                                <div style="border-top:1px solid #000; padding-top:5px; display:inline-block;">
                                    Sign of Class Coordinator
                                </div>
                            </div>
                            <div style="text-align:center; width:33%;">
                                <div style="border-top:1px solid #000; padding-top:5px; display:inline-block;">
                                    Sign of HOD
                                </div>
                            </div>
                            <div style="text-align:right; width:33%;">
                                <div style="border-top:1px solid #000; padding-top:5px; display:inline-block;">
                                    Sign of Principal
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@include('includes.footerJs')
<script>
    $(document).ready(function() {
        // Initially hide batch_div
        $('#batch_div').hide();

        // Toggle batch_div visibility on lecture_type change
        $('#lecture_type').on('change', function() {
            var selectedLectureType = $(this).val();
            if (selectedLectureType !== 'Lecture') {
                $('#batch_div').show();
            } else {
                $('#batch_div').hide();
            }
        });

        var table = $('#example').DataTable({
            //select: false,          
            paging: false,          // Enable pagination
            //pageLength: 500,        // Rows per page
            //lengthMenu: [5, 10, 25, 50, 100], // Page size options
            ordering: true,        // Enable sorting
            searching: true,       // Enable search box
            info: true,            // Show "Showing 1 to n of n entries"
            //autoWidth: false,
            dom: 'Bfrtip', 
            buttons: [ 
                { extend: 'csv', text: ' CSV', title: 'Subjectwise Detailed Semester Report' }, 
                { extend: 'print', text: ' PRINT', title: 'Subjectwise Detailed Semester Report',customize: function (win) {
                    $(win.document.body).find('h1').css('text-align', 'center').css('font-size', '20px').css('margin-top', '5px');
                    $(win.document.body).find('th, td').css('color', 'black').css('text-align', 'center').css('vertical-align', 'middle');
                    $(win.document.body).prepend(`{!! App\Helpers\get_school_details($grade_id ?? '', $standard_id ?? '', $division_id ?? '', $subject ?? '') !!}`);
                            
                    // Custom formatted date: DD-MM-YYYY hh:mmAM/PM
                    const now = new Date();
                    const day = String(now.getDate()).padStart(2, '0');
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const year = now.getFullYear();
                    let hours = now.getHours();
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;
                    const formattedDateTime = `${day}-${month}-${year} ${hours}:${minutes}${ampm}`;
                    // Add signature section to print view
                    $(win.document.body).append(`
                        <div style="margin-top: 60px; padding: 20px 0; width: 100%;color:black">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
                                <div style="text-align: left; width: 33%;">
                                    <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                        Sign of Coordinator
                                    </div>
                                </div>
                                <div style="text-align: center; width: 33%;">
                                    <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                        Sign of HOD.
                                    </div>
                                </div>
                                <div style="text-align: right; width: 33%;">
                                    <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                        Sign of Principal
                                    </div>
                                </div>
                            </div>
                            </div>
                            <div style="text-align: left; margin-top: 20px;">
                                Printed on: ${formattedDateTime}
                            </div>
                        </div>
                    `);
                        }},
            ],
        });
        var g = document.getElementById("grade");
        var grade = g.options[g.selectedIndex].text;

        var s = document.getElementById("standard");
        var standard = s.options[s.selectedIndex].text;

        var d = document.getElementById("division");
        var division = d.options[d.selectedIndex].text;

        $('#grade').attr('required', true);
        $('#standard').attr('required', true);
        $('#division').attr('required', true);

        // get subject lists 
        function loadSubjects(selectedStandard, selectedDivision, callback) {
                var path = "{{ route('ajax_LMS_StandardwiseSubject') }}";
                $('#subject').find('option').remove().end().append('<option value="">Select Subject</option>').val('');

                $.ajax({
                    url: path,
                    data: { std_id: selectedStandard },
                    success: function(result) {
                        console.log(result);
                        for (var i = 0; i < result.length; i++) {
                            $("#subject").append(
                                $("<option></option>")
                                    .val(result[i]['subject_id'])
                                    .html(result[i]['display_name'])
                            );
                        }

                        // ✅ Call the callback after subjects are loaded
                        if (typeof callback === "function") {
                            callback();
                        }
                    }
                });

        }

        // On load, if subject is set, trigger division change and select subject
        @if(isset($data['subject']))
            loadSubjects('{{ $standard_id }}', '{{ $division_id }}', function() {
                $("#subject").val('{{ $data['subject'] }}');
            });
        @endif

        $('#division').on('change', function() {
            var selectedStandard = $('#standard').val();
            var selectedDivision = $(this).val();
            loadSubjects(selectedStandard, selectedDivision);
        });

    });
</script>

<script>
    $(document).on('change', '#lecture_type', function() {
        var standard_id = $('#standard').val();
        var division_id = $('#division').val();
        var path = "{{ route('get_batch') }}";

        $.ajax({
            url: path,
            data: 'standard_id=' + standard_id + '&division_id=' + division_id,
            success: function(data) {
                let selectedLectureType = $('#lecture_type').val();
                if (selectedLectureType !== 'Lecture') {
                    $('#batch_div').show();

                    var batch_select_container = $('#batch_div');
                    var batch_select = $('#batch_sel');

                    if (Array.isArray(data) && data.length > 0) {
                        if (batch_select_container.length === 0) {
                            batch_select_container = $(
                                '<div class="col-md-3 form-group" id="batch_div"></div>');
                            $('#lecture_type').after(batch_select_container);

                            var batch_select_label = $(
                                '<label for="batch_sel">Batch</label>');
                            batch_select = $(
                                '<select id="batch_sel" class="form-control" name="batch_sel"></select>'
                                );
                            var defaultOption = '<option value="">--Select--</option>';
                            batch_select.append(defaultOption);

                            batch_select_container.append(batch_select_label);
                            batch_select_container.append(batch_select);
                        }

                        // Populate the batch options
                        data.forEach(function(value) {
                            var option = '<option value="' + value.id + '">' + value.title +
                                '</option>';
                            batch_select.append(option);
                        });

                        // Show batch_div only if lecture_type is not 'Lecture'
                        var lectureType = $('#lecture_type').val();
                        if (lectureType !== 'Lecture') {
                            $('#batch_div').show();
                        } else {
                            $('#batch_div').hide();
                        }

                        // On load, if batch is set, trigger lecture_type change and select batch
                        @if(isset($data['batch_id']))
                            $('#batch_sel').val('{{ $data['batch_id'] }}');
                        @endif
                    } else {
                        $('#batch_div').hide();
                    }
                } else {
                    $('#batch_div').hide();
                }
            }
        });
    });

    // On load, if batch is set, trigger lecture_type change to show batch div and select batch
    @if(isset($data['batch_id']))
        $('#lecture_type').trigger('change');
    @endif
</script>
@include('includes.footer')
