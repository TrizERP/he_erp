@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Deposit In Hand Report</h4>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table id="example" class="table table-striped">
                    <thead>
                    <tr>
                        <th>Standard/Semester</th>
                        <th>Collected</th>
                        <th>Returned</th>
                        <th>In Hand</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data['data'] as $row)
                        <tr>
                            <td>{{ $row['standard_name'] }}</td>
                            <td>&#8377; {{ number_format($row['collected'], 2) }}</td>
                            <td>&#8377; {{ number_format($row['returned'], 2) }}</td>
                            <td>&#8377; {{ number_format($row['in_hand'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr style="font-weight:bold;background:#f8f9fa;">
                        <td colspan="3" class="text-right">Total Deposit In Hand</td>
                        <td>&#8377; {{ number_format($data['total_in_hand'], 2) }}</td>
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
