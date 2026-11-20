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
            $grade_id = $standard_id = $division_id= $enrollment_no = $from_date = $to_date = '';

            if(isset($data['grade_id'])){
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            if(isset($data['enrollment_no']))
            {
                $enrollment_no = $data['enrollment_no'];
            }
            if(isset($data['from_date']))
            {
                $from_date = $data['from_date'];
            }
            if(isset($data['to_date']))
            {
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
            
            <form action="{{ route('fees_refund_report_fees') }}" method="POST">
                @csrf
                <div class="row">
                    {{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id,$standard_id,$division_id) }}
                     <div class="col-md-4 form-group">
                        <label>Enrollment No</label>
                        <input type="text" id="enrollment_no" name="enrollment_no"
                                 value="{{$enrollment_no}}" class="form-control">
                    </div>
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
                $total_refund_amount = 0;
                foreach($report_data as $r){
                    $total_refund_amount += isset($r['amount']) ? $r['amount'] : 0;
                }
            @endphp

            <div class="card">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered">
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
                                @endphp
                                
                                @foreach($report_data as $key => $row)
                                    <tr>
                                        <td>{{ $j }}</td>
                                        <td>{{ $row['receipt_no'] }}</td>
                                        <td>{{ $row['enrollment_no'] }}</td>
                                        <td>{{ $row['student_name'] }}</td>
                                        <td>{{ $row['grade_name'] }}</td>
                                        <td>{{ $row['std_name'] }}</td>
                                        <td>{{ $row['division_name'] }}</td>
                                        <td>{{ number_format($row['amount'], 2) }}</td>
                                        <td>{{ $row['payment_mode'] }}</td>
                                        <td>{{ $row['cheque_no'] ?? 'N/A' }}</td>
                                        <td>{{ $row['bank_name'] ?? 'N/A' }}</td>
                                        <td>{{ $row['bank_branch'] ?? 'N/A' }}</td>
                                        <td>{{ $row['remarks'] ?? ($row['refund_remarks'] ?? 'N/A') }}</td>
                                        <td>{{ $row['refund_date'] ?? ($row['receipt_date'] ?? '') }}</td>
                                        <td>{{ $row['refund_by'] ?? ($row['created_by'] ?? '') }}</td>
                                    </tr>
                                    @php
                                        $j++;
                                    @endphp
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7" style="text-align:right">Total:</th>
                                    <th>{{ number_format($total_refund_amount, 2) }}</th>
                                    <th colspan="7"></th>
                                </tr>
                            </tfoot>
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
            select: false,
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
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':visible',
                        format: {
                            body: function (data, row, column, node) {
                                return data.replace(/<[^>]*>/g, '');
                            }
                        }
                    },
                    customize: function (doc) {
                        doc.styles.tableHeader.fillColor = '#f2f2f2';
                        doc.styles.tableHeader.color = '#000';
                        doc.styles.tableHeader.bold = true;
                    }
                },
                {
                    extend: 'csv',
                    text: 'CSV',
                    title: 'Fees Refund Report',
                    exportOptions: {
                        columns: ':visible',
                        format: {
                            body: function (data, row, column, node) {
                                return data.replace(/<[^>]*>/g, '');
                            }
                        }
                    }
                },
                {
                    extend: 'excel',
                    text: 'EXCEL',
                    title: 'Fees Refund Report',
                    exportOptions: {
                        columns: ':visible',
                        format: {
                            body: function (data, row, column, node) {
                                return data.replace(/<[^>]*>/g, '');
                            }
                        }
                    }
                },
                {
                    extend: 'print',
                    text: 'PRINT',
                    title: 'Fees Refund Report',
                    customize: function (win) {
                        $(win.document.body).prepend(`{!! App\Helpers\get_school_details("", "", "") !!}`);
                        $(win.document.body).append(`<div style="text-align: right;margin-top:20px">Printed on: {{date('d-m-Y H:i:s')}}</div>`);
                    },
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                'pageLength'
            ],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();
                var intVal = function (i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                            i : 0;
                };
                var total = api.column(7, {page: 'current'}).data().reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
                $(api.column(7).footer()).html(total.toFixed(2));
            }
        });

        $('#example thead tr').clone(true).appendTo('#example thead');
        $('#example thead tr:eq(1) th').each(function (i) {
            var title = $(this).text();
            if (title == 'Sr.No.') {
                $(this).html('');
                return;
            }
            $(this).html('<input type="text" placeholder="Search ' + title + '" style="width:100%" />');

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