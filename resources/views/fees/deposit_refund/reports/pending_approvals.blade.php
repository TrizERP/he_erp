@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Pending Approvals Report</h4>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table id="example" class="table table-striped">
                    <thead>
                    <tr>
                        <th>Request No</th>
                        <th>Student</th>
                        <th>Gr.No.</th>
                        <th>Final Amount</th>
                        <th>Current Status</th>
                        <th>Days Pending</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data['data'] as $row)
                        <tr @if($row->days_pending >= 7) style="background:#fff3cd;" @endif>
                            <td>{{ $row->request_no }}</td>
                            <td>{{ $row->student_name }}</td>
                            <td>{{ $row->enrollment_no }}</td>
                            <td>&#8377; {{ number_format($row->final_refund_amount, 2) }}</td>
                            <td>{{ $row->status_title }}</td>
                            <td>{{ $row->days_pending }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('includes.footerJs')
    <script>$(document).ready(function () { $('#example').DataTable(); });</script>
    @include('includes.footer')
</div>
