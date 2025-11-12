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

        @if (!empty($data['student_data']))
            <div class="card" id="printableArea">

                {{-- ✅ SHOW SCHOOL DETAILS SAME AS PRINT --}}
                <div class="school-detail">
                     {!! App\Helpers\get_school_details($grade_id, $standard_id, $division_id) !!}
                </div>

                <div class="academic-year" style="text-align:center; font-size:15px; font-weight:600; margin-top:15px; font-family:Arial, Helvetica, sans-serif;">
                    Academic Year : {{ $syear }} - {{ $nextYear }}
                </div>

                <h1 class="report-title" style="text-align:center; font-size:20px; font-weight:700; text-transform:uppercase; margin-top:5px; margin-bottom:15px; font-family:Arial, Helvetica, sans-serif;">
                    Subject Month to Month Report
                </h1>


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

                    <div style="margin-top:60px; padding:20px 0; width:100%; color:black">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; width:100%;">
                            <div style="text-align:left; width:33%;">
                                <div style="border-top:1px solid #000; padding-top:5px; display:inline-block;">
                                    Sign of Faculty
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

    // ✅ DataTable Setup with Bold Border on Print
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
                    $(win.document.body).html($('#printableArea').html());

                    const style = `
                          <style>
@media print {
    @page {
        size: A4 portrait;
        margin: 0cm 1.2cm 2cm 1.2cm; /* remove top margin for perfect alignment */
        @bottom-right {
            content: "Page " counter(page) " / " counter(pages);
        }
    }

    body {
        font-family: Arial, Helvetica, sans-serif !important;
        background: white !important;
        color: black !important;
        margin: 0 !important;
        padding: 0 !important;
    }

     /* ✅ Hide export buttons */
    .dt-buttons,
    .btn {
        display: none !important;
    }

    /* ===== SCHOOL DETAILS ===== */
    .school-details {
        text-align: center !important;
        border: none !important;
        background: transparent !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
    }

    .school-details h1,
    .school-details h2,
    .school-details h3,
    .school-details h4 {
        color: black !important;
        white-space: nowrap !important;
        margin-top: 0 !important;
        margin-bottom: 5px !important;
        text-align: center !important;
    }

    /* ===== REPORT TABLE: BOLD BLACK BORDERS ===== */
    #example {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 3px solid black !important; /* 🔥 Bold outer border */
        background: white !important;
        box-sizing: border-box !important;
        margin-top: 0 !important;
        color: black !important;
    }

    #example th,
    #example td {
        border: 2px solid black !important; /* 🔥 Bold inner borders */
         color: #000000 !important;
        background: white !important;
        text-align: center !important;
        padding: 6px !important;
        font-size: 13px !important;
        font-family: Arial, Helvetica, sans-serif !important;
    }

    #example th {
        font-weight: bold !important;
        background-color: #f2f2f2 !important;
         color: #000000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* ===== SIGNATURE AREA ===== */
.signature-block {
    display: flex;
    justify-content: space-between !important;
    margin-top: 40px !important;
    padding: 0 20px !important;
    color: #000000 !important; 
    font-family: Arial, Helvetica, sans-serif !important;
}

/* Ensure all inner text in signature section stays black */
.signature-block * {
    color: #000000 !important;
}

/* Signature line with black border and text */
.signature-line {
    border-top: 2px solid #000000 !important;
    padding-top: 5px;
    text-align: center;
    width: 150px;
    color: #000000 !important;
    font-size: 13px !important;
    font-weight: normal !important;
    background: transparent !important;
}

    /* ===== FORCE BLACK TEXT EVERYWHERE ===== */
    * {
        color: black !important;
    }

    /* ===== FORCE BLACK TEXT EVERYWHERE ===== */
    *,
    h1, h2, h3, h4, h5, h6,
    th, td, p, span, label, strong, b, i, u {
        color: #000000 !important;
        background: transparent !important;
        border-color: #000000 !important;
        box-shadow: none !important;
    }
}
</style>


                    `;
                    $(win.document.head).append(style);
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




@include('includes.footer')
