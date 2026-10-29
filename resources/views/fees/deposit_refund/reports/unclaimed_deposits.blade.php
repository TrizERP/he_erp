@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Unclaimed Deposits Report</h4>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Student</th>
                        <th>Gr.No.</th>
                        <th>Mobile</th>
                        <th>Left On</th>
                        <th>Collected</th>
                        <th>Returned</th>
                        <th>Balance Unclaimed</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data['data'] as $row)
                        <tr>
                            <td>{{ $row['student_name'] }}</td>
                            <td>{{ $row['enrollment_no'] }}</td>
                            <td>{{ $row['mobile'] }}</td>
                            <td>{{ $row['left_on'] }}</td>
                            <td>{{ number_format($row['collected'], 2) }}</td>
                            <td>{{ number_format($row['returned'], 2) }}</td>
                            <td>{{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="6" class="text-right">Total Unclaimed</th>
                        <th>{{ number_format($data['total_unclaimed'], 2) }}</th>
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
                        title: 'Unclaimed Deposits Report',
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
                        title: 'Unclaimed Deposits Report',
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
                        title: 'Unclaimed Deposits Report',
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
                        title: 'Unclaimed Deposits Report',
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
                    var total = api.column(6, {page: 'current'}).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                    $(api.column(6).footer()).html(total.toFixed(2));
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
