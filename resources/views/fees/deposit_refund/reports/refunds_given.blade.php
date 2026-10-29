@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Refunds Given Report</h4>
            </div>
        </div>

        <div class="card">
            <form method="get" class="row">
                <div class="col-md-3 form-group">
                    <label>From Date</label>
                    <input type="text" name="from_date" value="{{ $data['from_date'] }}" class="form-control mydatepicker" autocomplete="off">
                </div>
                <div class="col-md-3 form-group">
                    <label>To Date</label>
                    <input type="text" name="to_date" value="{{ $data['to_date'] }}" class="form-control mydatepicker" autocomplete="off">
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
                        <th>Voucher No</th>
                        <th>Request No</th>
                        <th>Student</th>
                        <th>Gr.No.</th>
                        <th>Final Amount</th>
                        <th>Accounts Head</th>
                        <th>Principal</th>
                        <th>Paid On</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data['data'] as $row)
                        <tr>
                            <td>{{ $row->voucher_no }}</td>
                            <td>{{ $row->request_no }}</td>
                            <td>{{ $row->student_name }}</td>
                            <td>{{ $row->enrollment_no }}</td>
                            <td>{{ number_format($row->final_refund_amount, 2) }}</td>
                            <td>{{ $row->accounts_head_name }}</td>
                            <td>{{ $row->principal_name }}</td>
                            <td>{{ $row->paid_date }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="4" class="text-right">Total Refunded</th>
                        <th>{{ number_format($data['total_refunded'], 2) }}</th>
                        <th colspan="3"></th>
                    </tr>
                    </tfoot>
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
                        title: 'Refunds Given Report',
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
                        title: 'Refunds Given Report',
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
                        title: 'Refunds Given Report',
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
                        title: 'Refunds Given Report',
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
                    var total = api.column(4, {page: 'current'}).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                    $(api.column(4).footer()).html(total.toFixed(2));
                }
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
