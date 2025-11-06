{{--@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')--}}
@extends('layout')
@section('container')

<style>
#overlay {
    position: fixed;
    display: none;
    width: 100%;
    height: 100%;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(0,0,0,0.5);
    z-index: 2;
    cursor: pointer;
}

@media print {
    body { font-size: 12px; }
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; page-break-after: auto; }
    .print-footer {
        width: 100%;
        text-align: right;
        font-weight: 600;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        margin-top: 10px;
    }
}
</style>

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Student Certificate History</h4>
            </div>
        </div>

        {{-- Search Form --}}
        <div class="card">
            <form action="{{ route('student_certificate_report.create') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    {{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id??'', $standard_id??'', $division_id??'') }}
                    <div class="col-md-4 form-group">
                        <label>{{ App\Helpers\get_string('studentname') }}</label>
                        <input type="text" name="stu_name" class="form-control" @if(isset($data['stu_name'])) value="{{ $data['stu_name'] }}" @endif>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ App\Helpers\get_string('uniqueid') }}</label>
                        <input type="text" name="uniqueid" class="form-control" @if(isset($data['uniqueid'])) value="{{ $data['uniqueid'] }}" @endif>
                    </div>
                    <div class="col-md-12 form-group mt-4">
                        <center>
                            <input type="submit" name="submit" value="Search" class="btn btn-success">
                        </center>
                    </div>
                </div>
            </form>
        </div>

        {{-- Result Table --}}
        @if(isset($data['result_report']))
        @php
            $j = 1;
            $result_report = $data['result_report'];
        @endphp

        <div class="card">
            <div class="table-responsive" id="printableArea">
                <table id="example" class="table table-striped">
                    <thead>
                        <tr>
                            <th>SR NO</th>
                            <th>GR No</th>
                            <th>Student Name</th>
                            <th>Standard</th>
                            <th>Division</th>
                            <th>Certificate No.</th>
                            <th>Certificate Type</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result_report as $key => $value)
                            @php
                                $student_name = ($value['first_name'] ?? '-') . ' ' . ($value['middle_name'] ?? '-') . ' ' . ($value['last_name'] ?? '-');
                            @endphp
                            <tr>
                                <td>{{ $j++ }}</td>
                                <td>{{ $value['enrollment_no'] }}</td>
                                <td>{{ $student_name }}</td>
                                <td>{{ $value['standard_name'] }}</td>
                                <td>{{ $value['division_name'] }}</td>
                                <td>{{ $value['certificate_number'] }}</td>
                                <td>{{ $value['certificate_type'] }}</td>
                                <td>{{ $value['created_at'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="print-footer"></div>
            </div>
        </div>
        @endif
    </div>
</div>

@include('includes.footerJs')

<script>
$(document).ready(function() {
    $('#example').DataTable({
        select: true,
        lengthMenu: [[100, 500, 1000, -1], ['100', '500', '1000', 'Show All']],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                title: 'Student Certificate Report',
                pageSize: 'A4',
                orientation: 'portrait',
                exportOptions: {
                    columns: ':visible'
                },
                customize: function(doc) {
                    // Set bold table headers and borders
                    doc.styles.tableHeader.fillColor = '#f2f2f2';
                    doc.styles.tableHeader.color = '#000';
                    doc.styles.tableHeader.bold = true;
                    doc.styles.tableHeader.alignment = 'center';
                    doc.styles.tableHeader.fontSize = 10;

                    // Add borders to all cells
                    doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                    doc.content[1].table.body.forEach(function(row) {
                        row.forEach(function(cell) {
                            cell.border = [true, true, true, true]; // top,right,bottom,left
                        });
                    });

                    // Add page number to footer
                    doc['footer'] = function(page, pages) {
                        return {
                            columns: [
                                { text: 'Printed on: ' + new Date().toLocaleString('en-IN', { hour12: true }), alignment: 'left', margin: [20, 0] },
                                { text: 'Page ' + page.toString() + ' of ' + pages.toString(), alignment: 'right', margin: [0, 0, 20, 0] }
                            ],
                            fontSize: 10
                        }
                    };

                    // Set page margins
                    doc.pageMargins = [15, 50, 15, 40]; // left, top, right, bottom
                }
            },
            {
                extend: 'print',
                title: '',
                customize: function(win) {
                    const printable = win.document.body;
                    printable.innerHTML = document.getElementById('printableArea').innerHTML;

                    // Add school details
                    $(win.document.body).prepend(`{!! App\Helpers\get_school_details("", "", "") !!}`);

                    // Add bold table borders
                    const style = `
                        <style>
                            @media print {
                                body { font-family: Arial, Helvetica, sans-serif; }
                                table#example {
                                    width: 100% !important;
                                    border-collapse: collapse !important;
                                    border: 3px solid #000 !important;
                                }
                                table#example th,
                                table#example td {
                                    border: 2px solid #000 !important;
                                    padding: 6px 8px !important;
                                    text-align: center !important;
                                    font-size: 12px !important;
                                }
                                table#example thead th {
                                    background-color: #f2f2f2 !important;
                                    font-weight: bold !important;
                                    border-bottom: 3px solid #000 !important;
                                }
                                .print-footer {
                                    width: 100%;
                                    text-align: right;
                                    font-weight: 600;
                                    font-family: Arial, Helvetica, sans-serif;
                                    font-size: 12px;
                                    margin-top: 10px;
                                }
                                @page { size: A4 portrait; margin: 15mm; }
                            }
                        </style>
                    `;
                    $(win.document.head).append(style);

                    // Add print footer
                    const now = new Date();
                    const formatted = now.toLocaleString('en-IN', { hour12: true });
                    $(win.document.body).append(`
                        <div class="print-footer">Printed on: ${formatted}</div>
                    `);
                }
            },
            'csv', 'excel', 'pageLength'
        ]
    });
});
</script>


@include('includes.footer')
@endsection
