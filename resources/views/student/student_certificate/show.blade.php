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

        <div class="card">
            <form action="{{ route('student_certificate_report.create') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    {{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id??'', $standard_id??'', $division_id??'') }}
                    <div class="col-md-4 form-group">
                        <label>{{App\Helpers\get_string('studentname')}}</label>
                        <input type="text" name="stu_name" class="form-control" @if(isset($data['stu_name'])) value="{{$data['stu_name']}}" @endif>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{App\Helpers\get_string('uniqueid')}}</label>
                        <input type="text" name="uniqueid" class="form-control" @if(isset($data['uniqueid'])) value="{{$data['uniqueid']}}" @endif>
                    </div>
                    <div class="col-md-12 form-group mt-4">
                        <center>
                            <input type="submit" name="submit" value="Search" class="btn btn-success">
                        </center>
                    </div>
                </div>
            </form>
        </div>

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
                                <td>{{$j++}}</td>
                                <td>{{$value['enrollment_no']}}</td>
                                <td>{{$student_name}}</td>
                                <td>{{$value['standard_name']}}</td>
                                <td>{{$value['division_name']}}</td>
                                <td>{{$value['certificate_number']}}</td>
                                <td>{{$value['certificate_type']}}</td>
                                <td>{{$value['created_at']}}</td>
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
                extend: 'print',
                title: '',
                customize: function(win){
                    const printable = win.document.body;
                    printable.innerHTML = document.getElementById('printableArea').innerHTML;

                    const footer = win.document.querySelector('.print-footer');

                    const pageHeight = 1122; // Approx A4 height in px
                    const contentHeight = printable.scrollHeight;
                    const totalPages = Math.ceil(contentHeight / pageHeight);

                    // Clear footer and add individual page numbers
                    footer.innerHTML = '';
                    for(let i=1; i<=totalPages; i++){
                        let pageDiv = win.document.createElement('div');
                        pageDiv.style.position = 'absolute';
                        pageDiv.style.bottom = '0';
                        pageDiv.style.right = '20px';
                        pageDiv.style.width = '100%';
                        pageDiv.style.textAlign = 'right';
                        pageDiv.style.fontWeight = '600';
                        pageDiv.style.fontFamily = 'Arial, Helvetica, sans-serif';
                        pageDiv.style.fontSize = '12px';
                        pageDiv.style.top = (pageHeight * (i-1) + pageHeight - 20) + 'px';
                        pageDiv.innerText = `Page ${i} / ${totalPages}`;
                        printable.appendChild(pageDiv);
                    }
                }
            },
            'csv', 'excel', 'pageLength'
        ],
    });
});
</script>

@include('includes.footer')
@endsection
