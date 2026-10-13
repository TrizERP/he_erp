@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Deductions Made Report</h4>
            </div>
        </div>

        <div class="card">
            <form method="get" class="row">
                <div class="col-md-3 form-group">
                    <label>From Date</label>
                    <input type="text" name="from_date" value="{{ $data['from_date'] }}" class="form-control mydatepicker" autocomplete="off">
                </div>
                <div class="col-md-3 form-group">
                    <label>To Date</label>
                    <input type="text" name="to_date" value="{{ $data['to_date'] }}" class="form-control mydatepicker" autocomplete="off">
                </div>
                <div class="col-md-3 form-group" style="margin-top:28px;">
                    <input type="submit" value="Filter" class="btn btn-success">
                </div>
            </form>
        </div>

        <div class="card">
            <div class="box-title"><label>Deduction Totals by Type</label></div>
            <table class="table table-striped">
                <thead><tr><th>Deduction Title</th><th>Total Amount</th></tr></thead>
                <tbody>
                @foreach($data['by_title'] as $title => $amount)
                    <tr><td>{{ $title }}</td><td>&#8377; {{ number_format($amount, 2) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="box-title"><label>Deduction Detail</label></div>
            <div class="table-responsive">
                <table id="example" class="table table-striped">
                    <thead>
                    <tr>
                        <th>Voucher No</th>
                        <th>Student</th>
                        <th>Gr.No.</th>
                        <th>Deduction Title</th>
                        <th>Amount</th>
                        <th>Remarks</th>
                        <th>Paid On</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data['data'] as $row)
                        <tr>
                            <td>{{ $row->voucher_no }}</td>
                            <td>{{ $row->student_name }}</td>
                            <td>{{ $row->enrollment_no }}</td>
                            <td>{{ $row->title }}</td>
                            <td>&#8377; {{ number_format($row->amount, 2) }}</td>
                            <td>{{ $row->remarks }}</td>
                            <td>{{ $row->paid_date }}</td>
                        </tr>
                    @endforeach
                    <tr style="font-weight:bold;background:#f8f9fa;">
                        <td colspan="4" class="text-right">Total Deductions</td>
                        <td colspan="3">&#8377; {{ number_format($data['total_deductions'], 2) }}</td>
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
