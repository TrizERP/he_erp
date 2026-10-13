@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Deposit Refund - Pending Accounts Head Approval</h4>
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
                        <th>Gr.No.</th>
                        <th>Deposit Balance</th>
                        <th>Deductions</th>
                        <th>Final Amount</th>
                        <th>Reason</th>
                        <th>Days Pending</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data['data'] as $req)
                        <tr>
                            <td>{{ $req->request_no }}</td>
                            <td>{{ $req->student_name }}</td>
                            <td>{{ $req->enrollment_no }}</td>
                            <td>&#8377; {{ number_format($req->deposit_balance, 2) }}</td>
                            <td>&#8377; {{ number_format($req->deduction_total, 2) }}</td>
                            <td>&#8377; {{ number_format($req->final_refund_amount, 2) }}</td>
                            <td>{{ $req->reason }}</td>
                            <td>{{ $req->days_pending }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success"
                                        onclick="showActionForm({{ $req->id }}, 'approve');">Approve</button>
                                <button type="button" class="btn btn-sm btn-danger"
                                        onclick="showActionForm({{ $req->id }}, 'return');">Return</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal" id="actionModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form id="actionForm" method="post">
                    @csrf
                    <input type="hidden" name="decision" id="decisionField">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="actionModalTitle">Approve Request</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <label>Remarks</label>
                            <textarea name="remarks" class="form-control" id="remarksField"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        var actionUrlTemplate = "{{ route('deposit_refund_accounts_head_action', 0) }}";

        function showActionForm(id, decision) {
            $('#actionForm').attr('action', actionUrlTemplate.replace(/0$/, id));
            $('#decisionField').val(decision);
            $('#actionModalTitle').text(decision === 'approve' ? 'Approve Request' : 'Return Request to Clerk');
            $('#remarksField').prop('required', decision === 'return');
            $('#actionModal').modal('show');
        }

        $(document).ready(function () {
            $('#example').DataTable();
        });
    </script>
    @include('includes.footer')
</div>
