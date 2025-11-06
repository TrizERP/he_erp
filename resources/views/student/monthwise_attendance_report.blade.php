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
            $syear = session()->get('syear');
            $nextYear = $syear + 1;
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
                    {{ App\Helpers\SearchChain('2', 'single', 'grade,std,div', $grade_id, $standard_id, $division_id) }}

                    <div class="col-md-2 form-group">
                        <label for="">Type</label>
                        <select name="lecture_type" id="lecture_type" class="form-control" required>
                            <option value="">-Select Type-</option>
                            @foreach ($data['types'] as $k => $value)
                                <option value="{{ $value }}" @if(isset($data['lecture_type']) && $data['lecture_type']==$value) selected @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if (isset($data['batch']) && !empty($data['batchs']))
                        <div class="col-md-2 form-group" id="batch_div">
                            <label>-Select Batch-</label>
                            <select name="batch_sel" class="form-control" id="batch_sel" required="">
                                @foreach ($data['batchs'] as $batch)
                                    <option value="{{ $batch->id }}" @if ($data['batch_id'] == $batch->id) selected @endif>
                                        {{ $batch->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-2 form-group">
                        <label for="">Subject</label>
                        <select name="subject" id="subject" class="form-control" required>
                            <option value="">-Select Subject-</option>
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

        @if (isset($data['student_data']))
            @php
            @endphp

            <div class="card">
                @php
                    echo App\Helpers\get_school_details($grade_id, $standard_id, $division_id,$subject);
                     $getInstitutes = session()->get('getInstitutes');
                     $academicYears = session()->get('academicYears');
                @endphp

                <div style="text-align:center">
                    <span style="font-size: 15px; font-weight: 600; font-family: Arial, Helvetica, sans-serif !important; display:block; margin-top: 15px; margin-bottom: 5px;">
                        Academic Year :{{ $syear }} - {{ $nextYear }}
                    </span>
                </div>
               
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
                            @foreach($data['student_data'] as $stu)
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
                </div>
            </div>
        @endif
    </div>
</div>

@include('includes.footerJs')
<script>
    $(document).ready(function() {
        $('#batch_div').hide();

        $('#lecture_type').on('change', function() {
            var selectedLectureType = $(this).val();
            if (selectedLectureType !== 'Lecture') {
                $('#batch_div').show();
            } else {
                $('#batch_div').hide();
            }
        });

        var table = $('#example').DataTable({
            paging: false,
            ordering: true,
            searching: true,
            info: true,
            dom: 'Bfrtip', 
            buttons: [ 
                { extend: 'csv', text: ' CSV', title: 'Subjectwise Detailed Semester Report' }, 
                { 
                    extend: 'print', 
                    text: ' PRINT', 
                    title: 'Subjectwise Detailed Semester Report',
                    customize: function (win) {
                        $(win.document.body)
                            .find('h1')
                            .css('text-align', 'center')
                            .css('font-size', '20px')
                            .css('margin-top', '5px');

                        // ✅ Bold border styling for print
                        $(win.document.body).find('table')
                            .css('border-collapse', 'collapse')
                            .css('width', '100%');
                        $(win.document.body).find('th, td')
                            .css('border', '2px solid black')
                            .css('padding', '5px')
                            .css('text-align', 'center')
                            .css('vertical-align', 'middle')
                            .css('color', 'black');

                        $(win.document.body).prepend(`{!! App\Helpers\get_school_details($grade_id ?? '', $standard_id ?? '', $division_id ?? '', $subject ?? '') !!}
                            <h4 style="text-align:center; font-size:13px; font-weight:600; font-family:Arial, Helvetica, sans-serif;">
                                Academic Year: {{ $syear }} - {{ $nextYear }}
                            </h4>
                        `);
                    }
                },
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

        function loadSubjects(selectedStandard, selectedDivision, callback) {
            var path = "{{ route('ajax_LMS_StandardwiseSubject') }}";
            $('#subject').find('option').remove().end().append('<option value="">Select Subject</option>').val('');

            $.ajax({
                url: path,
                data: { std_id: selectedStandard },
                success: function(result) {
                    for (var i = 0; i < result.length; i++) {
                        $("#subject").append(
                            $("<option></option>")
                                .val(result[i]['subject_id'])
                                .html(result[i]['display_name'])
                        );
                    }
                    if (typeof callback === "function") callback();
                }
            });
        }

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
                            batch_select_container = $('<div class="col-md-2 form-group" id="batch_div"></div>');
                            $('#lecture_type').after(batch_select_container);

                            var batch_select_label = $('<label for="batch_sel">Batch</label>');
                            batch_select = $('<select id="batch_sel" class="form-control" name="batch_sel"></select>');
                            var defaultOption = '<option value="">--Select--</option>';
                            batch_select.append(defaultOption);

                            batch_select_container.append(batch_select_label);
                            batch_select_container.append(batch_select);
                        }

                        data.forEach(function(value) {
                            var option = '<option value="' + value.id + '">' + value.title + '</option>';
                            batch_select.append(option);
                        });

                        var lectureType = $('#lecture_type').val();
                        if (lectureType !== 'Lecture') {
                            $('#batch_div').show();
                        } else {
                            $('#batch_div').hide();
                        }

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

    @if(isset($data['batch_id']))
        $('#lecture_type').trigger('change');
    @endif
</script>

<style>
@media print {
    @page {
        @bottom-right {
            content: "Page " counter(page) " / " counter(pages);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: black;
        }
    }
}
</style>


@include('includes.footer')
