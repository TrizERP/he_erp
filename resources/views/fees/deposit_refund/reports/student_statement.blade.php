@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Student Deposit Statement</h4>
            </div>
        </div>

        <div id="printSection" class="card">
            <h5>{{ $data['student']->name }} ({{ $data['student']->enrollment_no }})</h5>
            <p>Mobile: {{ $data['student']->mobile }}</p>

            <h6>Deposit Collected</h6>
            <table class="table table-striped">
                <thead><tr><th>Receipt No</th><th>Date</th><th>Amount</th></tr></thead>
                <tbody>
                @foreach($data['receipts'] as $r)
                    <tr><td>{{ $r->receipt_no }}</td><td>{{ $r->receiptdate }}</td><td>&#8377; {{ number_format($r->deposit, 2) }}</td></tr>
                @endforeach
                <tr style="font-weight:bold;"><td colspan="2" class="text-right">Total Collected</td><td>&#8377; {{ number_format($data['collected'], 2) }}</td></tr>
                </tbody>
            </table>

            <h6>Refunds Given</h6>
            <table class="table table-striped">
                <thead><tr><th>Voucher No</th><th>Date</th><th>Amount</th></tr></thead>
                <tbody>
                @forelse($data['refunds'] as $r)
                    <tr><td>{{ $r->voucher_no }}</td><td>{{ $r->paid_date }}</td><td>&#8377; {{ number_format($r->final_refund_amount, 2) }}</td></tr>
                @empty
                    <tr><td colspan="3">No refunds given yet.</td></tr>
                @endforelse
                <tr style="font-weight:bold;"><td colspan="2" class="text-right">Total Returned</td><td>&#8377; {{ number_format($data['returned'], 2) }}</td></tr>
                </tbody>
            </table>

            <h5 class="text-right">Balance Available for Refund: &#8377; {{ number_format($data['balance'], 2) }}</h5>
        </div>

        <div class="col-md-12 form-group text-center mt-3">
            <button class="btn btn-success" onclick="window.print();">Print Statement</button>
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
