@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                <h4 class="page-title">Subject Month to Month Report</h4>
            </div>

        </div>
        @php
            $grade_id = $standard_id = $division_id = $from_date = $to_date = '';

            if (isset($data['grade_id'])) {
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            if (isset($data['from_date'])) {
                $from_date = $data['from_date'];
            }
            if (isset($data['to_date'])) {
                $to_date = $data['to_date'];
            }
            $getInstitutes = session()->get('getInstitutes');
            $academicYears = session()->get('academicYears');
            $month_name = [
                1 => 'Jan',
                2 => 'Feb',
                3 => 'Mar',
                4 => 'Apr',
                5 => 'May',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Aug',
                9 => 'Sep',
                10 => 'Oct',
                11 => 'Nov',
                12 => 'Dec',
            ];
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
        <form action="{{ route('month_to_month_report.index') }}" method="get">
            @csrf
            <div class="row">
                {{ App\Helpers\SearchChain('3', 'single', 'grade,std,div', $grade_id, $standard_id, $division_id) }}
                <div class="col-md-3 form-group">
                    <label for="">Type</label>
                    <select name="lecture_type" id="lecture_type" class="form-control" required>
                        <option value="">Select Type</option>
                        @foreach ($data['types'] as $k => $value)
                            <option value="{{ $value }}" @if(isset($data['lecture_type']) && $data['lecture_type']==$value) selected @endif>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label for="">Subject</label>
                    <select name="subject" id="subject" class="form-control" required>
                        <option value="">Select Subject</option>
                    </select>
                </div>
                @if (isset($data['batch_id']) && !empty($data['batchs']))
                    <div class="col-md-3 form-group" id="batch_div">
                        <label>Select Batch</label>
                        <select name="batch_sel" class="form-control" id="batch_sel" required="">
                            @foreach ($data['batchs'] as $batch)
                                <option value="{{ $batch->id }}" @if ($data['batch_id'] == $batch->id) selected @endif>
                                    {{ $batch->title }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-3 form-group">
                    <label for="">From</label>
                    <input type="text" class="form-control mydatepicker" name="from_month" autocomplete="off" value="{{$from_date}}">
                </div>
                <div class="col-md-3 form-group">
                    <label for="">To</label>
                    <input type="text" class="form-control mydatepicker" name="to_month" autocomplete="off" value="{{$to_date}}">
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
            $j = 1;
            if (isset($data['student_data'])) {
                $student_data = $data['student_data'];
            }
        @endphp
        <div class="card">
            @php
                echo App\Helpers\get_school_details($grade_id, $standard_id, $division_id);
                // Safely output month and year if they exist
                $displayMonth = isset($data['month']) ? ($month_name[$data['month']] ?? '') : '';
                $displayYear  = isset($data['year']) ? $data['year'] : '';
                if ($displayMonth || $displayYear) {
                    echo '<br><center><span style="font-size:14px;font-weight:600;font-family:Arial,Helvetica,sans-serif!important">Month : ' .
                         e($displayMonth) .
                         ' / </span><span style="font-size:14px;font-weight:600;font-family:Arial,Helvetica,sans-serif!important">Year : ' .
                         e($displayYear) .
                         '</span></center><br>';
                }
            @endphp
            <h1 style="text-align: center; font-size: 20px; margin-top: 5px;">Subject Month to Month Report</h1>
            <div class="table-responsive">
    <table id="example" class="table display" style="border:none !important">
        <thead>
            <tr>
                <th>Sr</th>
                <th>Enrollment No</th>
                <th>Student Name</th>
                @foreach ($data['month_totals'] as $monthId => $total)
                    <th class="text-center">{{ $month_name[(int)$monthId] ?? '' }} ({{ $total }})</th>
                @endforeach

                @php
                    // Calculate grand total of all months
                    $grand_total = array_sum($data['month_totals']);
                @endphp

                <th class="text-center">Total ({{ $grand_total }})</th> <!-- 👈 Added total month header -->
            </tr>
        </thead>

        <tbody>
            @foreach ($data['student_data'] as $key => $student)
                @php
                    $student_total = 0;
                @endphp
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $student['enrollment_no'] }}</td>
                    <td>{{ $student['student_name'] }}</td>
                    @foreach ($data['month_totals'] as $monthId => $total)
                        @php
                            $month_value = $student[$monthId] ?? 0;
                            $student_total += $month_value;
                        @endphp
                        <td class="text-center">{{ $month_value }}</td>
                    @endforeach
                    <td class="text-center fw-bold">{{ $student_total }}</td> <!-- 👈 Student-wise total -->
                </tr>
            @endforeach
        </tbody>
    </table>
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
        ordering: false,        // Enable sorting
        searching: false,       // Enable search box
        info: false,            // Show "Showing 1 to n of n entries"
        //autoWidth: false,
        dom: 'Bfrtip', 
        buttons: [ 
            { extend: 'csv', text: ' CSV', title: 'Subject Month to Month Report' }, 
            { extend: 'print', text: ' PRINT', title: 'Subject Month to Month Report',customize: function (win) {
                $(win.document.body).find('h1').css('text-align', 'center').css('font-size', '20px').css('margin-top', '5px');
                $(win.document.body).find('th, td').css('color', 'black').css('text-align', 'center').css('vertical-align', 'middle');
                $(win.document.body).prepend(`{!! App\Helpers\get_school_details($grade_id ?? '', $standard_id ?? '', $division_id ?? '') !!}`);
                        
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
        $('#example thead #heads').clone(true).appendTo('#example thead');
        $('#example thead #heads:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" placeholder="Search ' + title + '" />');

            $('input', this).on('keyup change', function() {
                if (table.column(i).search() !== this.value) {
                    table
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });

        });

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
                            $('#std_div').before(batch_select_container);

                            var batch_select_label = $(
                                '<label for="batch_sel">Select Batch</label>');
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
