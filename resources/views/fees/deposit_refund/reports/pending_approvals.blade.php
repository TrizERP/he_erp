@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Approvals Report</h4>
            </div>
        </div>

        @php
            $from_date = isset($data['from_date']) ? $data['from_date'] : '';
            $to_date = isset($data['to_date']) ? $data['to_date'] : '';
            $approval_status = isset($data['approval_status']) ? $data['approval_status'] : '';
        @endphp

        <div class="card">
            <form method="get" class="row">
                <div class="col-md-3 form-group">
                    <label>From Date</label>
                    <input type="text" name="from_date" value="{{ $from_date }}" class="form-control mydatepicker" autocomplete="off">
                </div>
                <div class="col-md-3 form-group">
                    <label>To Date</label>
                    <input type="text" name="to_date" value="{{ $to_date }}" class="form-control mydatepicker" autocomplete="off">
                </div>
                <div class="col-md-3 form-group">
                    <label>Principal Status</label>
                    <select name="approval_status" class="form-control">
                        <option value="">All</option>
                        <option value="pending" {{ $approval_status == 'pending' ? 'selected' : '' }}>Pending Principal Approval</option>
                        <option value="approved" {{ $approval_status == 'approved' ? 'selected' : '' }}>Approved - Pending Payment</option>
                    </select>
                </div>
                <div class="col-md-3 form-group" style="margin-top:28px;">
                    <input type="submit" value="Filter" class="btn btn-success">
                </div>
            </form>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Request No</th>
                        <th>Student</th>
                        <th>Gr.No.</th>
                        <th>Final Amount</th>
                        <th>Current Status</th>
                        <th>Bank Account No</th>
                        <th>IFSC Code</th>
                        <th>Requested Date</th>
                        <th>Days Pending</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data['data'] as $row)
                        <tr @if($row->days_pending >= 7) style="background:#fff3cd;" @endif>
                            <td>{{ $row->request_no }}</td>
                            <td>{{ $row->student_name }}</td>
                            <td>{{ $row->enrollment_no }}</td>
                            <td>{{ number_format($row->final_refund_amount, 2) }}</td>
                            <td>{{ $row->status_title }}</td>
                            <td>{{ $row->bank_account_number }}</td>
                            <td>{{ $row->ifsc_code }}</td>
                            <td>{{ $row->requested_date }}</td>
                            <td>{{ $row->days_pending }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
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
                    title: 'Approvals Report',
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
                    title: 'Approvals Report',
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
                    title: 'Approvals Report',
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
                    title: 'Approvals Report',
                        customize: function (win) {
                            $(win.document.body).prepend(`{!! App\Helpers\get_school_details("", "", "") !!}`);
                            $(win.document.body).append(`<div style="text-align: right;margin-top:20px">Printed on: {{date('d-m-Y H:i:s')}}</div>`);
                        },
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    'pageLength'
                ]
            });

            $('#example thead tr').clone(true).appendTo('#example thead');
            $('#example thead tr:eq(1) th').each(function (i) {
                var title = $(this).text();
                if (title == '') {
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
</div>
