@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Deposit Refund Voucher</h4>
            </div>
        </div>

        @if(isset($data['message']))
            <div class="alert alert-success text-center">
                <strong>{{ $data['message'] }}</strong>
            </div>
        @endif

        @if(isset($data['voucher_html']))
            <div id="printSection" class="card">
                {!! $data['voucher_html'] !!}
            </div>
            <div class="col-md-12 form-group text-center mt-3">
                <button class="btn btn-success" onclick="window.print();">Print Voucher</button>
                <a href="{{ route('deposit_refund_payment.index') }}" class="btn btn-secondary">Back to Payment Queue</a>
            </div>
        @endif
    </div>

    @include('includes.footerJs')
    @include('includes.footer')
</div>
