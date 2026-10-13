@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <h4 class="page-title">My Deposit Refund Requests</h4>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                <a href="{{ route('deposit_refund.index') }}" class="btn btn-secondary">New Search</a>
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
                        <th>Final Amount</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data['data'] as $req)
                        <tr>
                            <td>{{ $req->request_no }}</td>
                            <td>{{ $req->student_name }}</td>
                            <td>{{ $req->enrollment_no }}</td>
                            <td>&#8377; {{ number_format($req->final_refund_amount, 2) }}</td>
                            <td>{{ $req->status_title }}</td>
                            <td>{{ $req->requested_date }}</td>
                            <td>
                                @if(in_array($req->status, [2, 4]))
                                    <form action="{{ route('deposit_refund_resubmit', $req->id) }}" method="post" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary"
                                                onclick="return confirm('Resubmit this request for Accounts Head approval?');">
                                            Resubmit
                                        </button>
                                    </form>
                                @endif
                                @if(!in_array($req->status, [6, 7]))
                                    <button type="button" class="btn btn-sm btn-danger" onclick="showCancelForm({{ $req->id }});">
                                        Cancel
                                    </button>
                                @endif
                                @if($req->status == 6)
                                    <a href="{{ route('deposit_refund_print_voucher', $req->id) }}" class="btn btn-sm btn-info" target="_blank">
                                        View Voucher
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal" id="cancelModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form id="cancelForm" method="post">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Cancel Refund Request</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <label>Cancellation Reason <span class="text-danger">*</span></label>
                            <textarea name="cancellation_reason" class="form-control" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        var cancelUrlTemplate = "{{ route('deposit_refund_cancel', 0) }}";

        function showCancelForm(id) {
            $('#cancelForm').attr('action', cancelUrlTemplate.replace(/0$/, id));
            $('#cancelModal').modal('show');
        }

        $(document).ready(function () {
            $('#example').DataTable();
        });
    </script>
    @include('includes.footer')
</div>
