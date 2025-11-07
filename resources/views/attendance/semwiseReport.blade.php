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
                    <select class="form-control" name="report_type">
                        @foreach ($report_type as $key => $value)
                            <option value="{{ $key }}" @if (isset($data['report_type']) && $data['report_type'] == $value) selected @endif>
                                {{ $value }}</option>
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
                    // Add school details and report title
                    $(win.document.body)
                        .prepend(`{!! App\Helpers\get_school_details($grade_id ?? '', $standard_id ?? '', $division_id ?? '') !!}
                            <h4 style="text-align:center; font-size:13px; font-weight:600; font-family:Arial, Helvetica, sans-serif;">
                                Academic Year: {{ $syear }} - {{ $nextYear }}
                            </h4>
                            <h1 style="text-align:center; font-size:20px; margin-top:5px; font-weight:700; font-family:Arial, Helvetica, sans-serif;">
                                All Subject Semesterwise Report
                            </h1>`);

                    // ✅ Hide % column in print if Percentage wise is selected
                    var reportType = $('select[name="report_type"]').val();
                    if (reportType === 'pw') {
                        $(win.document.body).find('table#example th:last-child, table#example td:last-child').remove();
                    }

                    // Add print styles for bold borders and formatting
                    const style = `
                        <style>
                            @media print {
                                body {
                                    font-family: Arial, Helvetica, sans-serif !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }

                                table#example {
                                    width: 100% !important;
                                    border-collapse: collapse !important;
                                    border: 3px solid black !important;
                                    margin-top: 10px !important;
                                }

                                table#example th,
                                table#example td {
                                    border: 2px solid black !important;
                                    padding: 6px 8px !important;
                                    text-align: center !important;
                                    font-size: 12px !important;
                                }

                                table#example thead th {
                                    background-color: #f2f2f2 !important;
                                    font-weight: bold !important;
                                    border-bottom: 3px solid black !important;
                                }

                                table#example tbody tr:nth-child(odd) {
                                    background-color: #f9f9f9 !important;
                                }

                                table#example tbody tr:nth-child(even) {
                                    background-color: #ffffff !important;
                                }

                                .signature-block {
                                    margin-top: 60px;
                                    display: flex;
                                    justify-content: space-between;
                                    width: 100%;
                                }

                                .signature {
                                    border-top: 3px solid #000;
                                    padding-top: 5px;
                                    width: 30%;
                                    text-align: center;
                                }

                                .print-footer {
                                    margin-top: 20px;
                                    font-size: 12px;
                                    text-align: left;
                                }

                                @page {
                                    size: A4 landscape;
                                    margin: 15mm 10mm 20mm 10mm;
                                    @bottom-right {
                                        content: "Page " counter(page) " / " counter(pages);
                                        font-size: 12px;
                                        font-family: Arial, Helvetica, sans-serif;
                                    }
                                }
                            }
                        </style>
                    `;
                    $(win.document.head).append(style);

                    // Append signatures and print date
                    const now = new Date();
                    const formatted = now.toLocaleString('en-IN', { hour12: true });
                    $(win.document.body).append(`
                        <div class="signature-block">
                            <div class="signature">Sign of Faculty</div>
                            <div class="signature">Sign of HOD</div>
                            <div class="signature">Sign of Principal</div>
                        </div>
                        <div class="print-footer">Printed on: ${formatted}</div>
                    `);
                }
            }
        ]
    });

    // ✅ Hide/show % column on screen dynamically based on report type
    function togglePercentageColumn() {
        var reportType = $('select[name="report_type"]').val();
        var lastColumnIndex = table.columns().count() - 1; // index of last column

        if (reportType === 'pw') {
            table.column(lastColumnIndex).visible(false); // hide %
        } else {
            table.column(lastColumnIndex).visible(true); // show %
        }
    }

    togglePercentageColumn(); // initial check on load

    $('select[name="report_type"]').on('change', function() {
        togglePercentageColumn();
    });
});
</script>



@endsection
