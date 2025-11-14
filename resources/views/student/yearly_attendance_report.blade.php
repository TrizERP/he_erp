@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Yearly Attendance Report</h4>
            </div>
        </div>

        @php
            $grade_id = $standard_id = $division_id = '';
            if(isset($data['grade_id'])){
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }

            $getInstitutes = session()->get('getInstitutes');
            $academicYears = session()->get('academicYears');
            $syear = session()->get('syear');
            $nextYear = $syear + 1;
        @endphp

        <div class="card shadow-sm p-4">
            @if ($sessionData = Session::get('data'))
                <div class="alert alert-{{ $sessionData['status_code'] == 1 ? 'success' : 'danger' }} alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $sessionData['message'] }}</strong>
                </div>
            @endif

            <form action="{{ route('show_yearly_student_attendance') }}" enctype="multipart/form-data" method="post">
                @csrf
                <div class="row">
                    {{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id,$standard_id,$division_id) }}

                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-semibold">From Date</label>
                        <input type="text" id="from_date" name="from_date"
                            value="@if(isset($data['from_date'])){{$data['from_date']}}@endif"
                            class="form-control mydatepicker" autocomplete="off" required>
                    </div>

                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-semibold">To Date</label>
                        <input type="text" id="to_date" name="to_date"
                            value="@if(isset($data['to_date'])){{$data['to_date']}}@endif"
                            class="form-control mydatepicker" autocomplete="off" required>
                    </div>

                    <div class="col-md-12 form-group text-center mt-3 mb-2">
                        <input type="submit" name="submit" value="Search" class="btn btn-success px-4 py-2">
                    </div>
                </div>
            </form>
        </div>

        @if(isset($data['student_data']))
            @php
                $j = 1;
                $student_data = $data['student_data'];
            @endphp

            <div class="card mt-4 shadow-sm">
                <div class="table-responsive p-4" id="printPage">
                    <!-- ✅ School details (no border) -->
                    <div id="school-details" style="border:none;">
                        @php
                            echo App\Helpers\get_school_details($grade_id,$standard_id,$division_id);
                            echo '<div style="margin-top:5px; text-align:center;">
                                    <span style="font-size:16px; font-weight:bold; color:#007b33;">
                                        Academic Year : '.$syear.' - '.$nextYear.'
                                    </span><br>
                                    <span style="font-size:13px; font-weight:600; color:#000;">
                                        From Date : '.date('d-m-Y',strtotime($data['from_date'])).' &nbsp;&nbsp; | &nbsp;&nbsp;
                                        To Date : '.date('d-m-Y',strtotime($data['to_date'])).'
                                    </span>
                                  </div><br>';
                        @endphp
                    </div>

                    <!-- ✅ Main table -->
                    <table class="table table-bordered table-striped table-hover" border="1">
                        @php 
                            $month_name = [1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "May", 6 => "June", 7 => "July", 8 => "Aug", 9 => "Sept", 10 => "Oct", 11 => "Nov", 12 => "Dec"]; 
                        @endphp
                        <thead class="text-center bg-light">
                            <tr>
                                <th>Sr No</th>
                                <th>{{App\Helpers\get_string('grno','request')}}</th>
                                <th>{{App\Helpers\get_string('studentname','request')}}</th>
                                @foreach($data['month'] as $i)
                                    <th>{{$month_name[$i]}}</th>
                                @endforeach
                                <th>Total School Year Day</th>
                            </tr>

                            <tr>
                                <th>Sr No</th>
                                <th>{{App\Helpers\get_string('grno','request')}}</th>
                                <th>{{App\Helpers\get_string('studentname','request')}}</th>
                                @php $working_day = 0; @endphp
                                @foreach($data['month'] as $i)
                                    <th style="text-align:center">
                                        @if(isset($data['working_day'][$i]))
                                            @php $working_day += $data['working_day'][$i]; @endphp
                                            {{ $data['working_day'][$i] }}
                                        @else
                                            0
                                        @endif
                                    </th>
                                @endforeach
                                <th style="text-align:center">{{$working_day}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student_data as $value)
                                <tr>
                                    @php $totalAttandance = 0; @endphp
                                    <td class="text-center">{{$j++}}</td>
                                    <td class="text-center">{{$value['enrollment_no']}}</td>
                                    <td>{{$value['first_name']." ".$value['middle_name']." ".$value['last_name']}}</td>
                                    @foreach($data['month'] as $ii)
                                        <td class="text-center">
                                            @if(isset($data['attendance_data'][$value['id']][$ii]))
                                                {{$data['attendance_data'][$value['id']][$ii]}}
                                                @php $totalAttandance += $data['attendance_data'][$value['id']][$ii]; @endphp
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center">{{$totalAttandance}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-12 form-group text-center mt-4">
                <button class="btn btn-success px-4 py-2" onclick="PrintDiv('printPage');">Print</button>
            </div>
        @endif
    </div>
</div>

@include('includes.footerJs')

<script>
function PrintDiv(divName) {
    var divToPrint = document.getElementById(divName);
    var popupWin = window.open('', '_blank', 'width=1000,height=800');
    popupWin.document.open();
    popupWin.document.write(`
        <html>
        <head>
            <title>Yearly Attendance Report</title>
            <style>

            #school-details {
    text-align: center;
    white-space: nowrap; /* ✅ keeps long name in one line */
    font-size: 20px;
    font-weight: bold;
    color: #007b33;
    margin-bottom: 5px;
}

#school-details * {
    white-space: nowrap !important; /* applies to inner elements too */
}

    body { 
        font-family: Arial; 
        font-size: 16px;
        margin: 10mm 10mm 15mm 10mm; /* proper A4 spacing */
    }

    /* ❌ Remove borders in school header details */
    #school-details table,
    #school-details th,
    #school-details td {
        border: none !important;
        background: transparent !important;
    }

    /* ✅ Apply borders only to main report table */
    table.table {
        width: 100%;
        border-collapse: collapse;
    }
    table.table th, 
    table.table td {
        border: 2px solid #000;
        padding: 5px;
        text-align: center;
    }
    table.table th {
        font-weight: bold;
        background: #f9f9f9;
    }

    /* ✅ A4 layout and bottom-right page number */
    @page {
        size: A4 portrait;
        margin: 10mm;
        @bottom-right {
            content: "Page " counter(page) " / " counter(pages);
            font-weight: bold;
            font-size: 13px;
        }
    }

    @media print {
        html, body {
            width: 210mm;
            height: 297mm;
        }
        button, .print-footer { display: none !important; }
    }
</style>

        </head>
        <body onload="window.print()">
            ${divToPrint.innerHTML}
        </body>
        </html>
    `);
    popupWin.document.close();
}
</script>

<style>


/* Apply strong borders only to the main report table */
#printPage .table,
#printPage .table th,
#printPage .table td {
    border: 2px solid #000 !important;
    border-collapse: collapse !important;
}
</style>

@include('includes.footer')
