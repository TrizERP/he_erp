@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper" style="color:#000;">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <h4 class="page-title">Deposit Refund Request</h4>
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
                        <tr><td>Student Enrolment No</td><td><b>{{ $stu['enrollment_no'] }}</b></td></tr>
                        <tr><td>Student Name</td><td><b>{{ $stu['name'] }}</b></td></tr>
                        <tr><td>Semester/Division</td><td><b>{{ $stu['stddiv'] }}</b></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-striped">
                        <tr><td>Deposit Collected</td><td><b>&#8377; {{ number_format($ledger['collected'], 2) }}</b></td></tr>
                        <tr><td>Deposit Already Returned</td><td><b>&#8377; {{ number_format($ledger['returned'], 2) }}</b></td></tr>
                        <tr><td>Balance Available for Refund</td><td><b id="depositBalance" data-value="{{ $ledger['balance'] }}">&#8377; {{ number_format($ledger['balance'], 2) }}</b></td></tr>
                        <tr><td>Unpaid Fees of Student</td><td style="color:red;"><b>&#8377; {{ number_format($data['unpaid_fees'], 2) }}</b></td></tr>
                    </table>
                </div>
            </div>

            <form action="{{ route('deposit_refund_store') }}" method="post" enctype="multipart/form-data" id="refundForm">
                @csrf
                <input type="hidden" name="student_id" value="{{ $stu['student_id'] }}">

                <div class="row">
                    <div class="col-md-12 form-group">
                        <label>Reason for Refund <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control" required placeholder="e.g. Transfer Certificate issued">
                    </div>
                </div>



                <div class="row mt-3">
                    <div class="col-md-4 form-group">
                        <label>Name of Person Receiving Money <span class="text-danger">*</span></label>
                        <input type="text" name="receiver_name" class="form-control" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Relation with Student</label>
                        <input type="text" name="receiver_relation" class="form-control" placeholder="e.g. Father">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>How Money Will Be Paid <span class="text-danger">*</span></label>
                        <select class="form-control" name="payment_mode" id="payment_mode" required onchange="toggleModeFields();">
                            <option value="">--Select--</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <div class="row chequeFields" style="display:none;">
                    <div class="col-md-4 form-group">
                        <label>Cheque Number</label>
                        <input type="text" name="cheque_no" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Cheque Date</label>
                        <input type="text" name="cheque_date" class="form-control mydatepicker" autocomplete="off">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Bank Name</label>
                        <select class="form-control" name="bank_name">
                            <option value="">--Select--</option>
                            @foreach($data['bank_data'] as $bank)
                                <option value="{{ $bank->bank_name }}">{{ $bank->bank_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row bankFields" style="display:none;">
                    <div class="col-md-4 form-group">
                        <label>Bank Account Number</label>
                        <input type="text" name="bank_account_number" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>IFSC Code</label>
                        <input type="text" name="ifsc_code" class="form-control">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6 form-group">
                        <label>Supporting Document</label>
                        <input type="file" name="supporting_document" class="form-control-file">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Remarks</label>
                        <textarea name="clerk_remarks" class="form-control"></textarea>
                    </div>
                </div>

                <div class="col-md-12 form-group text-center mt-3">
                    <input type="submit" value="Submit for Approval" class="btn btn-success">
                </div>
            </form>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        function toggleModeFields() {
            var mode = $('#payment_mode').val();
            $('.chequeFields').toggle(mode === 'Cheque');
            $('.bankFields').toggle(mode === 'Bank Transfer');
        }
    </script>
    @include('includes.footer')
</div>
