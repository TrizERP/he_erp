@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Fees Refund Report</h4>
            </div>
        </div>
        
        @php
            $grade_id = $standard_id = $division_id = $from_date = $to_date = '';

            if(isset($data['grade_id'])){
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
                $from_date = $data['from_date'] ?? '';
                $to_date = $data['to_date'] ?? '';
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
            
            <form action="{{ route('fees_refund_report') }}" method="POST">
                @csrf
                <div class="row">
                    {{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id,$standard_id,$division_id) }}

                    <div class="col-md-4 form-group">
                        <label>From Date</label>
                        <input type="text" id="from_date" name="from_date" value="{{ $from_date }}" 
                               class="form-control mydatepicker" required="required" autocomplete="off">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>To Date</label>
                        <input type="text" id="to_date" name="to_date" value="{{ $to_date }}"
                               class="form-control mydatepicker" required="required" autocomplete="off">
                    </div>

                    <div class="col-md-12 form-group">
                        <center>
                            <input type="submit" name="submit" value="Search" class="btn btn-success">
                        </center>
                    </div>
                </div>
            </form>
        </div>

        @if(isset($data['report_data']))
            @php
                if(isset($data['report_data'])){
                    $report_data = $data['report_data'];
                    $finalData = $data;
                }
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
                                @php
                                    $j = 1;
                                    $total_refund_amount = 0;
                                @endphp
                                
                                @foreach($report_data as $key => $data)
                                    @php
                                        $total_refund_amount += $data['amount'];
                                    @endphp
                                    <tr>
                                        <td>{{ $j }}</td>
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
                                    @php
                                        $j++;
                                    @endphp
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
</div>

@include('includes.footerJs')
<script>
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
                    title: 'Fees Refund Report',
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    exportOptions: {
                        columns: ':visible'
                    },
                    customize: function (doc) {
                        // Add footer with total amount
                        doc.content.push({
                            text: 'Total Refund Amount: {{ $total_refund_amount ?? 0 }}',
                            alignment: 'right',
                            margin: [0, 10, 0, 0]
                        });
                    }
                },
                {extend: 'csv', text: ' CSV', title: 'Fees Refund Report'},
                {extend: 'excel', text: ' EXCEL', title: 'Fees Refund Report'},
                {extend: 'print', text: ' PRINT', title: 'Fees Refund Report'},
                'pageLength'
            ],
        });

        $('#example thead tr').clone(true).appendTo('#example thead');
        $('#example thead tr:eq(1) th').each(function (i) {
            var title = $(this).text();
            $(this).html('<input type="text" placeholder="Search ' + title + '" />');

            $('input', this).on('keyup change', function () {
                if (table.column(i).search() !== this.value) {
                    table
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });
    });
</script>

@include('includes.footer')