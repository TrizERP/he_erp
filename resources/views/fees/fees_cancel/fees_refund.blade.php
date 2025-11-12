@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
      rel="stylesheet">
<style>
    .toggle.btn.btn-danger {
        width: 200px !important;
    }

    .toggle.btn.btn-warning {
        width: 200px !important;
    }
</style>

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Fees Refund</h4>
            </div>
        </div>

        <div class="row bg-title">
            <div class="col-md-3 d-flex">
                <input type="checkbox" id="toggle_cancel_refund" name="toggle_cancel_refund" checked
                       data-toggle="toggle" data-on="Fees Refund" data-off="Fees Cancel" data-onstyle="danger"
                       data-offstyle="warning" onchange="show_fees_cancel_refund();">
            </div>
        </div>

        @php
            $grade_id = $standard_id = $division_id = $enrollment_no = $from_date = $to_date = '';

            if(isset($data['grade_id'])){
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            if(isset($data['enrollment_no'])){
                $enrollment_no = $data['enrollment_no'];
            }
            if(isset($data['from_date'])){
                $from_date = $data['from_date'];
            }
            if(isset($data['to_date'])){
                $to_date = $data['to_date'];
            }
        @endphp

        <div class="card">
            @if ($sessionData = Session::get('data'))
                @if($sessionData['status_code'] == 1)
                    <div class="alert alert-success alert-block">
                @else
                    <div class="alert alert-danger alert-block">
                @endif
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <strong>{{ $sessionData['message'] }}</strong>
                    </div>
            @endif

            {{-- ===== Search Form ===== --}}
            <form action="{{ route('fees_refund_report') }}" method="post">
                {{ method_field("POST") }}
                @csrf
                <div class="row">
                    {{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id,$standard_id,$division_id) }}

                    {{-- ✅ Added Enrollment No field here --}}
                    <div class="col-md-4 form-group">
                        <label>Enrollment No</label>
                        <input type="text" id="enrollment_no" name="enrollment_no"
                               value="{{ $enrollment_no }}" class="form-control" placeholder="Enter Enrollment No">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>From Date</label>
                        <input type="text" id="from_date" name="from_date" value="{{ $from_date }}"
                               class="form-control mydatepicker" required autocomplete="off">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>To Date</label>
                        <input type="text" id="to_date" name="to_date" value="{{ $to_date }}"
                               class="form-control mydatepicker" required autocomplete="off">
                    </div>

                    <div class="col-md-12 form-group">
                        <center>
                            <input type="submit" name="submit" value="Search" class="btn btn-success">
                        </center>
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== Report Table Section ===== --}}
        @if(isset($data['report_data']))
            @php
                $report_data = $data['report_data'];
                $total_refund_amount = 0;
            @endphp

            <div class="card">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        <table id="example" class="table table-striped">
                            <thead>
                            <tr>
                                <th>Sr.No.</th>
                                <th>Receipt No.</th>
                                 <th>{{ App\Helpers\get_string('grno','request')}}</th>
                                <th>{{ App\Helpers\get_string('studentname','request')}}</th>
                                <th>Grade</th>
                                <th>{{ App\Helpers\get_string('standard','request')}}</th>
                                <th>{{ App\Helpers\get_string('division','request')}}</th>
                                <th>Refund Amount</th>
                                <th>Payment Mode</th>
                                <th>Cheque No</th>
                                <th>Bank Name</th>
                                <th>Bank Branch</th>
                                <th>Refund Remarks</th>
                                <th>Refund Date</th>
                                <th>Refund By</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php $j = 1; @endphp
                            @foreach($report_data as $key => $data)
                                @php $total_refund_amount += $data['amount']; @endphp
                                <tr>
                                    <td>{{ $j++ }}</td>
                                    <td>{{ $data['receipt_no'] }}</td>
                                    <td>{{ $data['enrollment_no'] }}</td>
                                    <td>{{ $data['student_name'] }}</td>
                                    <td>{{ $data['grade_name'] }}</td>
                                    <td>{{ $data['std_name'] }}</td>
                                    <td>{{ $data['division_name'] }}</td>
                                    <td>{{ $data['amount'] }}</td>
                                    <td>{{ $data['payment_mode'] }}</td>
                                    <td>{{ $data['cheque_no'] ?? 'N/A' }}</td>
                                    <td>{{ $data['bank_name'] ?? 'N/A' }}</td>
                                    <td>{{ $data['bank_branch'] ?? 'N/A' }}</td>
                                    <td>{{ $data['refund_remarks'] ?? 'N/A' }}</td>
                                    <td>{{ $data['refund_date'] }}</td>
                                    <td>{{ $data['refund_by'] }}</td>
                                </tr>
                            @endforeach

                            {{-- Total Row --}}
                            <tr style="font-weight: bold; background-color: #f8f9fa;">
                                <td colspan="7" style="text-align: right;">Total Refund Amount:</td>
                                <td>{{ $total_refund_amount }}</td>
                                <td colspan="7"></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('includes.footerJs')
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>

    <script>
        function show_fees_cancel_refund() {
            if ($("#toggle_cancel_refund").prop("checked") == true) {
                var path = "{{ route('fees_refund.index') }}";
                location.href = path;
            } else {
                var path1 = "{{ route('fees_cancel.index') }}";
                location.href = path1;
            }
        }

        $(document).ready(function () {
            var table = $('#example').DataTable({
                select: true,
                lengthMenu: [
                    [100, 500, 1000, -1],
                    ['100', '500', '1000', 'Show All']
                ],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'pdfHtml5',
                        title: 'Fees Refund',
                        orientation: 'landscape',
                        pageSize: 'LEGAL',
                        exportOptions: {
                            columns: ':visible'
                        },
                        customize: function (doc) {
                            doc.content.push({
                                text: 'Total Refund Amount: {{ $total_refund_amount ?? 0 }}',
                                alignment: 'right',
                                margin: [0, 10, 0, 0]
                            });
                        }
                    },
                    {extend: 'csv', text: ' CSV', title: 'Fees Refund'},
                    {extend: 'excel', text: ' EXCEL', title: 'Fees Refund'},
                    {extend: 'print', text: ' PRINT', title: 'Fees Refund'},
                    'pageLength'
                ],
            });

            $('#example thead tr').clone(true).appendTo('#example thead');
            $('#example thead tr:eq(1) th').each(function (i) {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="Search ' + title + '" />');

                $('input', this).on('keyup change', function () {
                    if (table.column(i).search() !== this.value) {
                        table.column(i).search(this.value).draw();
                    }
                });
            });
        });
    </script>

    @include('includes.footer')

    <style type="text/css">
        @media screen {
            #printSection {
                display: none;
            }
        }
    </style>
