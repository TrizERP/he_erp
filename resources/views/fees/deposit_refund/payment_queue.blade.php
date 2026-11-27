@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Deposit Refund - Approved, Pending Payment</h4>
            </div>
        </div>

        @if ($sessionData = Session::get('data'))
            <div class="alert alert-{{ $sessionData['status_code'] == 1 ? 'success' : 'danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $sessionData['message'] }}</strong>
            </div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table id="example" class="table table-striped">
                    <thead>
                    <tr>
                        <th>Request No</th>
                        <th>Student</th>
                        <th>Enrollment No.</th>
                        <th>Receiver</th>
                        <th>Payment Mode</th>
                        <th>Final Amount</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data['data'] as $req)
                        <tr>
                            <td>{{ $req->request_no }}</td>
                            <td>{{ $req->student_name }}</td>
                            <td>{{ $req->enrollment_no }}</td>
                            <td>{{ $req->receiver_name }}</td>
                            <td>
                                {{ $req->payment_mode }}
                                @if($req->payment_mode == 'Cheque')
                                    ({{ $req->cheque_no }}, {{ $req->cheque_date }})
                                @elseif($req->payment_mode == 'Bank Transfer')
                                    ({{ $req->bank_account_number }}, {{ $req->ifsc_code }})
                                @endif
                            </td>
                            <td>&#8377; {{ number_format($req->final_refund_amount, 2) }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success" onclick="showPayForm(
                                        {{ $req->id }},
                                        '{{ addslashes($req->payment_mode) }}',
                                        '{{ addslashes($req->bank_name ?? '') }}',
                                        '{{ addslashes($req->bank_branch ?? '') }}',
                                        '{{ addslashes($req->bank_account_number ?? '') }}',
                                        '{{ addslashes($req->ifsc_code ?? '') }}'
                                    );">
                                    Pay &amp; Print Voucher
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal" id="payModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form id="payForm" method="post" target="_blank">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Payment</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Payment Mode</label>
                                <input type="text" id="modal_payment_mode" class="form-control" readonly>
                            </div>
                            <div id="modal_bank_details" style="display:none;">
                                <div class="form-group">
                                    <label>Bank Name</label>
                                    <input type="text" id="modal_bank_name" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Bank Branch</label>
                                    <input type="text" id="modal_bank_branch" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Account Number</label>
                                    <input type="text" id="modal_account_number" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>IFSC Code</label>
                                    <input type="text" id="modal_ifsc_code" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Payment Reference / Transaction Note</label>
                                <input type="text" name="payment_reference" class="form-control"
                                       placeholder="Cheque handed over / NEFT UTR no. etc.">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Confirm Payment &amp; Print Voucher</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        var payUrlTemplate = "{{ route('deposit_refund_pay', 0) }}";

        function showPayForm(id, paymentMode, bankName, bankBranch, accountNumber, ifscCode) {
            $('#payForm').attr('action', payUrlTemplate.replace(/0$/, id));
            $('#modal_payment_mode').val(paymentMode);
            if (paymentMode === 'Bank Transfer') {
                $('#modal_bank_name').val(bankName);
                $('#modal_bank_branch').val(bankBranch);
                $('#modal_account_number').val(accountNumber);
                $('#modal_ifsc_code').val(ifscCode);
                $('#modal_bank_details').show();
            } else {
                $('#modal_bank_details').hide();
            }
            $('#payModal').modal('show');
        }

        $(document).ready(function () {
            $('#example').DataTable({
                order: [[0, 'asc']]
            });
        });
    </script>
    @include('includes.footer')
</div>
