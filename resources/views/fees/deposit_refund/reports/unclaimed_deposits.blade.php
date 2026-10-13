@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Unclaimed Deposits Report</h4>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table id="example" class="table table-striped">
                    <thead>
                    <tr>
                        <th>Student</th>
                        <th>Gr.No.</th>
                        <th>Mobile</th>
                        <th>Left On</th>
                        <th>Collected</th>
                        <th>Returned</th>
                        <th>Balance Unclaimed</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data['data'] as $row)
                        <tr>
                            <td>{{ $row['student_name'] }}</td>
                            <td>{{ $row['enrollment_no'] }}</td>
                            <td>{{ $row['mobile'] }}</td>
                            <td>{{ $row['left_on'] }}</td>
                            <td>&#8377; {{ number_format($row['collected'], 2) }}</td>
                            <td>&#8377; {{ number_format($row['returned'], 2) }}</td>
                            <td>&#8377; {{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr style="font-weight:bold;background:#f8f9fa;">
                        <td colspan="6" class="text-right">Total Unclaimed</td>
                        <td>&#8377; {{ number_format($data['total_unclaimed'], 2) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('includes.footerJs')
    <script>$(document).ready(function () { $('#example').DataTable(); });</script>
    @include('includes.footer')
</div>
