@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <h4 class="page-title">Student Deposit Account</h4>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                <a href="{{ route('deposit_refund.index') }}" class="btn btn-secondary">Back to Search</a>
            </div>
        </div>

        @if ($sessionData = Session::get('data'))
            <div class="alert alert-{{ $sessionData['status_code'] == 1 ? 'success' : 'danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $sessionData['message'] }}</strong>
            </div>
        @endif

        @php $stu = $data['stu_data']; $ledger = $data['ledger']; @endphp

        <div class="card">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-striped">
                        <tr><td>Student Name</td><td>{{ $stu['name'] }}</td></tr>
                        <tr><td>Gr.No.</td><td>{{ $stu['enrollment_no'] }}</td></tr>
                        <tr><td>Standard/Division</td><td>{{ $stu['stddiv'] }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-striped">
                        <tr><td>Deposit Collected</td><td>&#8377; {{ number_format($ledger['collected'], 2) }}</td></tr>
                        <tr><td>Deposit Already Returned</td><td>&#8377; {{ number_format($ledger['returned'], 2) }}</td></tr>
                        <tr style="font-weight:bold;"><td>Balance Available for Refund</td><td>&#8377; {{ number_format($ledger['balance'], 2) }}</td></tr>
                    </table>
                </div>
            </div>
            @if(isset($data['legacy_data']) && $data['legacy_data'])
                <div class="row">
                    <div class="col-md-12">
                        <h5 style="margin-left:15px;">Deposit Details</h5>
                        <table class="table table-striped">
                            <tr>
                                <td>Receipt No</td>
                                <td>{{ $data['legacy_data']->reciept_no ?? '-' }}</td>
                                <td>Received Date</td>
                                <td>{{ $data['legacy_data']->received_date ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Payment Mode</td>
                                <td>{{ $data['legacy_data']->payment_mode ?? '-' }}</td>
                                <td>Bank Name</td>
                                <td>{{ $data['legacy_data']->bank_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Bank Branch</td>
                                <td>{{ $data['legacy_data']->bank_branch ?? '-' }}</td>
                                <td>Cheque/DD No</td>
                                <td>{{ $data['legacy_data']->cheque_dd_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Cheque/DD Date</td>
                                <td>{{ $data['legacy_data']->cheque_dd_date ?? '-' }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endif
            <div class="col-md-12 text-center mt-3">
                <a href="{{ route('deposit_refund_report_student_statement', $stu['student_id']) }}" class="btn btn-outline-secondary" target="_blank">
                    Print Student Statement
                </a>
                @if(!$data['has_active_request'] && $ledger['balance'] > 0)
                    <a href="{{ route('deposit_refund_create', $stu['student_id']) }}" class="btn btn-success">Refund</a>
                @elseif($data['has_active_request'])
                    <span class="badge badge-warning" style="font-size:14px;">A refund request is already in progress for this student</span>
                @else
                    <span class="badge badge-secondary" style="font-size:14px;">No deposit balance available for refund</span>
                @endif
            </div>
        </div>

        @if(count($data['history']) > 0)
            <div class="card">
                <div class="box-title"><label>Refund Request History</label></div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Request No</th>
                            <th>Voucher No</th>
                            <th>Final Amount</th>
                            <th>Status</th>
                            <th>Requested On</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data['history'] as $req)
                            <tr>
                                <td>{{ $req['request_no'] }}</td>
                                <td>{{ $req['voucher_no'] ?? 'N/A' }}</td>
                                <td>&#8377; {{ number_format($req['final_refund_amount'], 2) }}</td>
                                <td>
                                    @php
                                        $statusLabels = [
                                            1 => 'Pending Accounts Head Approval', 2 => 'Returned by Accounts Head',
                                            3 => 'Pending Principal Approval', 4 => 'Returned by Principal',
                                            5 => 'Approved - Pending Payment', 6 => 'Paid', 7 => 'Cancelled',
                                        ];
                                    @endphp
                                    {{ $statusLabels[$req['status']] ?? $req['status'] }}
                                </td>
                                <td>{{ $req['requested_date'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
