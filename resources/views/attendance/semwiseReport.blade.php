@extends('layout')
@section('container')

<div id="page-wrapper">
    <div class="container-fluid">

        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">All Subject Semesterwise Report</h4>
            </div>
        </div>
    </div>

    @php
        $grade_id = $standard_id = $division_id = $from_date = $to_date = '';
        $syear = session()->get('syear');
        $nextYear = $syear + 1;
        $batch = [];
        if (isset($data['from_date'])) {
            $from_date = $data['from_date'];
        }
        if (isset($data['to_date'])) {
            $to_date = $data['to_date'];
        }
        if (isset($data['batch'])) {
            $batch = $data['batch'];
        }
        if (isset($data['grade_id'])) {
            $grade_id = $data['grade_id'];
            $standard_id = $data['standard_id'];
            $division_id = $data['division_id'];
        }
        $att_type = ['Lecture', 'Lab', 'Tutorial'];
        $report_type = ['pw' => 'Percentage wise', 'nw' => 'Number of Lectures wise'];
    @endphp

    <div class="card">
        <form action="{{ route('semwise_report.create') }}">
            @csrf
            <div class="row">

                {{ App\Helpers\SearchChain('2', 'single', 'grade,std,div', $grade_id, $standard_id, $division_id) }}

                <div class="form-group col-md-2">
                    <label>Type</label>
                    <select class="form-control" name="att_type" id="att_type" required>
                        <option>Select</option>
                        @foreach ($att_type as $key => $value)
                            <option value="{{ $value }}" @if (isset($data['attendance_type']) && $data['attendance_type'] == $value) selected @endif>
                                {{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-2" id="batch_div">
                    <label>Batch</label>
                    <select class="form-control" name="batch" id="batch">
                        @if (!empty($batch))
                            @foreach ($batch as $key => $value)
                                <option value="{{ $value->id }}" @if (isset($data['batch_id']) && $data['batch_id'] == $value->id) selected @endif>
                                    {{ $value->title }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

               <div class="form-group col-md-2">
    <label>Report Type</label>
    <select class="form-control" name="report_type" required>
        <option value="">Select</option>
        @foreach ($report_type as $key => $value)
            <option value="{{ $key }}" @if (isset($data['report_type']) && $data['report_type'] == $key) selected @endif>
                {{ $value }}
            </option>
        @endforeach
    </select>
</div>


                <div class="form-group col-md-2">
                    <label>Below Percent(%)</label>
                    <input type="number" class="form-control" name="below_percent"
                        @if (isset($data['below_percent'])) value="{{ $data['below_percent'] }}" @endif autocomplete="off">
                </div>

                <div class="form-group col-md-2">
                    <label>From Date</label>
                    <input type="text" id="from_date" name="from_date" value="{{ $from_date }}"
                        class="form-control mydatepicker" autocomplete="off" required>
                </div>

                <div class="form-group col-md-2">
                    <label>To Date</label>
                    <input type="text" id="to_date" name="to_date" value="{{ $to_date }}"
                        class="form-control mydatepicker" autocomplete="off" required>
                </div>

            </div>

            <div class="col-md-12 form-group">
                <center>
                    <input type="submit" name="submit" value="Search" class="btn btn-success">
                </center>
            </div>
        </form>
    </div>

    @if (!empty($data['header']))
    <div class="card">
        <form action="{{ route('semwise_report.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="table-responsive">
                    {!! App\Helpers\get_school_details("$grade_id", "$standard_id", "$division_id") !!}

                    {{-- ✅ Academic Year Label Added Here --}}
                    @php
                         $getInstitutes = session()->get('getInstitutes');
                         $academicYears = session()->get('academicYears');
                    @endphp
                    <h4 style="text-align: center; font-size: 15px; font-weight: 600; font-family: Arial, Helvetica, sans-serif; margin-top: 8px;">
                        Academic Year: {{ $syear }} - {{ $nextYear }}
                    </h4>

                    {{-- ✅ Report Title Below Academic Year --}}
                    <h1 style="text-align: center; font-size: 20px; margin-top: 5px; font-family: Arial, Helvetica, sans-serif; font-weight: 700;">
                        All Subject Semesterwise Report
                    </h1>

               <style>
/* =============================
   PRINT-ONLY BOLD TABLE BORDER
============================= */
@media print {
    #example {
        border-collapse: collapse !important;
        width: 100% !important;
        border: 2px solid black !important;
    }

    #example th,
    #example td {
        border: 2px solid black !important;
        padding: 6px !important;
        text-align: center !important;
    }

    /* Optional: make font more readable in print */
    #example th {
        font-weight: bold !important;
        background-color: #f5f5f5 !important;
        -webkit-print-color-adjust: exact; /* Keep background in print */
    }
}
</style>
                    <table id="example" class="table table-striped">
                        <thead>
                            <tr>
                                <th>SR No <input type="checkbox" name="checkAll" id="checkAll"> </th>
                                <th>{{ App\Helpers\get_string('grno', 'request') }}</th>
                                <th>{{ App\Helpers\get_string('studentname', 'request') }}</th>
                                @php $tot = 0; $i = 1; @endphp
                                @foreach ($data['header'] as $index => $value)
                                    <th>{{ $value->short_name }}({{ $value->TOTAL_LEC }})</th>
                                    @php $tot += isset($value->TOTAL_LEC) ? $value->TOTAL_LEC : 0; @endphp
                                @endforeach
                                <th>Total({{ $tot }})</th>
                                <th class="text-left">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($data['details']))
                                @foreach ($data['details'] as $key => $val)
                                    <tr>
                                        <td>{{ $i++ }} <input type="checkbox" name="student[{{ $val['student_id'] }}]" id="singleCheck"></td>
                                        <td>{{ $val['enrollment_no'] }}</td>
                                        <td>{{ $val['student_name'] }}</td>
                                        @foreach ($data['header'] as $index => $value)
                                            <td>{{ $val['COURSE_' . $value->subject_id] ?? 0 }}</td>
                                        @endforeach
                                        <td>{{ $val['TOTAL'] }}</td>
                                        <td>{{ $val['TOTAL_PERCENTAGE'] }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>

                    <div style="margin-top: 60px; padding: 20px 0; width: 100%; color:black">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
                            <div style="text-align: left; width: 33%;">
                                <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                    Sign of Faculty
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

                <div class="col-md-12">
                    <center>
                        <input type="submit" value="Send SMS" class="btn btn-success">
                    </center>
                </div>
            </div>
        </form>
    </div>
    @endif
</div>
@include('includes.footerJs')

<script type="text/javascript">
$(document).ready(function() {
    $('#batch_div').hide();
    var selectVal = $('#att_type').val();
    var batchs = @json($data['batch'] ?? []);
    var batch_id = '{{ $data['batch_id'] ?? '' }}';

    if (batch_id && batchs.length > 0 && selectVal != "Lecture") {
        $('#batch_div').show();
    }

    $('#att_type').on('change', function() {
        var type = $(this).val();
        var standard = $('#standard').val();
        var division = $('#division').val();

        if (type != "Lecture") {
            $.ajax({
                type: "GET",
                url: '/get-batch?standard=' + standard + '&division=' + division,
                success: function(res) {
                    $('#batch_div').show();
                    $('#batch').empty();
                    if (res) {
                        $.each(res, function(key, value) {
                            $("#batch").append('<option value="' + value.id + '">' + value.title + '</option>');
                        });
                    }
                }
            });
        } else {
            $('#batch_div').hide();
        }
    });

    // jQuery checkAll functionality
    $('#checkAll').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('input[name^="student["]').prop('checked', isChecked);
    });
});
</script>

@include('includes.footer')
<script>
$(document).ready(function() {
    var table = $('#example').DataTable({
        paging: false,
        ordering: false,
        searching: false,
        info: false,
        dom: 'Bfrtip',
        buttons: [
            { 
                extend: 'csv', 
                text: 'CSV', 
                title: 'All Subject Semesterwise Report' 
            },
            { 
                extend: 'print',
                text: 'PRINT',
                title: '',
                customize: function (win) {
                    // ✅ Add school details and titles at the top
                    $(win.document.body)
                        .prepend(`{!! App\Helpers\get_school_details($grade_id ?? '', $standard_id ?? '', $division_id ?? '') !!}
                            <h4 style="text-align:center; font-size:13px; font-weight:600; font-family:Arial, Helvetica, sans-serif;">
                                Academic Year: {{ $syear }} - {{ $nextYear }}
                            </h4>
                            <h1 style="text-align:center; font-size:20px; margin-top:5px; font-weight:700; font-family:Arial, Helvetica, sans-serif;">
                                All Subject Semesterwise Report
                            </h1>`);

                    // ✅ Add strong bold border + page number + signatures
                    const style = `
                        <style>
                        @media print {

                            /* ====== PAGE SETTINGS ====== */
                            @page {
                                size: A4 portrait;
                                margin: 1.2cm 1.5cm 2cm 1.5cm;
                            }

                            body {
                                font-family: Arial, Helvetica, sans-serif !important;
                                font-size: 12px !important;
                                color: black !important;
                            }

                            /* ====== BOLD TABLE BORDER ====== */
                            #example {
                                border-collapse: collapse !important;
                                width: 100% !important;
                                border: 2px solid black !important;
                            }

                            #example th,
                            #example td {
                                border: 2px solid black !important;
                                padding: 6px !important;
                                text-align: center !important;
                                vertical-align: middle !important;
                            }

                            #example th {
                                font-weight: bold !important;
                                background-color: #f5f5f5 !important;
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                            }

                            /* ====== SIGNATURE BLOCK ====== */
                            .signature-block {
                                width: 100%;
                                margin-top: 60px;
                                padding-top: 20px;
                                display: flex;
                                justify-content: space-between;
                                align-items: flex-start;
                            }

                            .signature-block div {
                                width: 33%;
                                text-align: center;
                                font-size: 13px;
                                color: black;
                            }

                            .signature-line {
                                display: inline-block;
                                border-top: 1px solid black;
                                padding-top: 5px;
                                margin-top: 50px;
                            }

                            /* ====== PRINT FOOTER ====== */
                            .print-footer {
                                margin-top: 15px;
                                text-align: right;
                                font-size: 11px;
                                color: black;
                                font-family: Arial, Helvetica, sans-serif;
                            }

                            /* PAGE NUMBER (bottom-right corner) */
                            @page {
                                @bottom-right {
                                    content: "Page " counter(page) " / " counter(pages);
                                    font-size: 11px;
                                    font-family: Arial, Helvetica, sans-serif;
                                }
                            }
                        }
                        </style>
                    `;
                    $(win.document.head).append(style);

                    // ✅ Keep signature block aligned exactly like non-print version
                    $(win.document.body).append(`
                        <div class="signature-block">
                            <div><div class="signature-line">Sign of Faculty</div></div>
                            <div><div class="signature-line">Sign of HOD</div></div>
                            <div><div class="signature-line">Sign of Principal</div></div>
                        </div>
                        <div class="print-footer">
                            Printed on: ${new Date().toLocaleString('en-IN', { hour12: true })}
                        </div>
                    `);
                }
            }
        ]
    });
});
</script>


@endsection
