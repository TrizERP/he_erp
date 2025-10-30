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
            if (isset($data['from_date'])) $from_date = $data['from_date'];
            if (isset($data['to_date'])) $to_date = $data['to_date'];

            $getInstitutes = session()->get('getInstitutes');
            $academicYears = session()->get('academicYears');
            $syear = session()->get('syear');
            $nextYear = $syear + 1;

            $month_name = [
                1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
                7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'
            ];
        @endphp

        <div class="card">
            @if ($sessionData = Session::get('data'))
                <div class="alert alert-{{ $sessionData['status_code'] == 1 ? 'success' : 'danger' }} alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $sessionData['message'] }}</strong>
                </div>
            @endif

            <form action="{{ route('month_to_month_report.index') }}" method="get">
                @csrf
                <div class="row">
                    {{ App\Helpers\SearchChain('3','single','grade,std,div',$grade_id,$standard_id,$division_id) }}

                    <div class="col-md-3 form-group">
                        <label>Type</label>
                        <select name="lecture_type" id="lecture_type" class="form-control" required>
                            <option value="">Select Type</option>
                            @foreach ($data['types'] as $value)
                                <option value="{{ $value }}" @selected(isset($data['lecture_type']) && $data['lecture_type'] == $value)>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Subject</label>
                        <select name="subject" id="subject" class="form-control" required>
                            <option value="">Select Subject</option>
                        </select>
                    </div>

                    @if (isset($data['batch_id']) && !empty($data['batchs']))
                        <div class="col-md-3 form-group" id="batch_div">
                            <label>Select Batch</label>
                            <select name="batch_sel" id="batch_sel" class="form-control" required>
                                @foreach ($data['batchs'] as $batch)
                                    <option value="{{ $batch->id }}" @selected($data['batch_id'] == $batch->id)>{{ $batch->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-3 form-group">
                        <label>From</label>
                        <input type="text" class="form-control mydatepicker" name="from_month" autocomplete="off" value="{{ $from_date }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>To</label>
                        <input type="text" class="form-control mydatepicker" name="to_month" autocomplete="off" value="{{ $to_date }}">
                    </div>

                    <div class="col-md-12 form-group text-center">
                        <input type="submit" name="submit" value="Search" class="btn btn-success">
                    </div>
                </div>
            </form>
        </div>

        {{-- ===================== REPORT SECTION ===================== --}}
        @if (isset($data['student_data']))
            @php
                echo App\Helpers\get_school_details($grade_id, $standard_id, $division_id);
            @endphp

            {{-- ✅ Academic Year Label --}}
            <center>
                <span style="font-size: 15px; font-weight: 600; font-family: Arial, Helvetica, sans-serif !important; display:block; margin-top: 15px; margin-bottom: 5px;">
                    Academic Year : {{ $syear }} - {{ $nextYear }}
                </span>
            </center>

            {{-- ✅ Report Title --}}
            <h1 style="text-align:center; font-size:20px; font-weight:700; text-transform:uppercase; margin-top:5px; margin-bottom:15px; font-family: Arial, Helvetica, sans-serif !important;">
                Subject Month to Month Report
            </h1>

            <div class="card">
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
                                @php $grand_total = array_sum($data['month_totals']); @endphp
                                <th class="text-center">Total ({{ $grand_total }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['student_data'] as $key => $student)
                                @php $student_total = 0; @endphp
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
                                    <td class="text-center fw-bold">{{ $student_total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- ✅ Signature Section --}}
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
    $('#batch_div').hide();

    $('#lecture_type').on('change', function() {
        var selectedLectureType = $(this).val();
        if (selectedLectureType !== 'Lecture') {
            $('#batch_div').show();
        } else {
            $('#batch_div').hide();
        }
    });

    // ✅ DataTable Setup
    var table = $('#example').DataTable({
        paging: false,
        ordering: false,
        searching: false,
        info: false,
        dom: 'Bfrtip',
        buttons: [
            { extend: 'csv', text: ' CSV', title: 'Subject Month to Month Report' },
            {
                extend: 'print',
                text: ' PRINT',
                title: '',
                customize: function (win) {
                    $(win.document.body).find('th, td').css({'color':'black','text-align':'center'});
                    $(win.document.body).prepend(`{!! App\Helpers\get_school_details($grade_id ?? '', $standard_id ?? '', $division_id ?? '') !!}`);
                    $(win.document.body).prepend(`<center><span style='font-size:12px;font-weight:600;font-family:Arial,Helvetica,sans-serif !important;display:block;margin-top:15px;margin-bottom:5px;'>Academic Year : {{ $syear }} - {{ $nextYear }}</span></center>`);
                    $(win.document.body).prepend("<h1 style='text-align:center;font-size:20px;font-weight:700;text-transform:uppercase;margin-top:5px;margin-bottom:15px;font-family:Arial,Helvetica,sans-serif !important;'>Subject Month to Month Report</h1>");
                }
            }
        ]
    });

    // ✅ Load subjects dynamically
    function loadSubjects(selectedStandard, selectedDivision, callback) {
        var path = "{{ route('ajax_LMS_StandardwiseSubject') }}";
        $('#subject').html('<option value=\"\">Select Subject</option>');
        $.ajax({
            url: path,
            data: { std_id: selectedStandard },
            success: function(result) {
                result.forEach(function(r) {
                    $("#subject").append($("<option></option>").val(r['subject_id']).html(r['display_name']));
                });
                if (callback) callback();
            }
        });
    }

    @if(isset($data['subject']))
        loadSubjects('{{ $standard_id }}', '{{ $division_id }}', function() {
            $("#subject").val('{{ $data['subject'] }}');
        });
    @endif

    $('#division').on('change', function() {
        loadSubjects($('#standard').val(), $(this).val());
    });
});
</script>

@include('includes.footer')
