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

        @php
            $grade_id = $data['grade_id'] ?? '';
            $standard_id = $data['standard_id'] ?? '';
            $division_id = $data['division_id'] ?? '';
        @endphp
        <div class="card">
            <form method="get" class="row">
                {{ App\Helpers\SearchChain('4', 'single', 'grade,std,div', $grade_id, $standard_id, $division_id) }}
                <div class="col-md-3 form-group">
                    <label>From Date</label>
                    <input type="text" name="from_date" value="{{ $data['from_date'] }}" class="form-control mydatepicker" autocomplete="off">
                </div>
                <div class="col-md-3 form-group">
                    <label>To Date</label>
                    <input type="text" name="to_date" value="{{ $data['to_date'] }}" class="form-control mydatepicker" autocomplete="off">
                </div>
                <div class="col-md-12 form-group">
                    <center>
                        <input type="submit" name="submit" value="Search" class="btn btn-success">
                    </center>
                </div>
            </form>
        </div>

        @if (!empty($data['searched']))
        <div class="card">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Voucher No</th>
                        <th>Request No</th>
                        <th>Section</th>
                        <th>Semester</th>
                        <th>Division</th>
                        <th>Student</th>
                        <th>Enrollment No.</th>
                        <th>Admission Year</th>
                        <th>Final Amount</th>
                        <th>Bank Name</th>
                        <th>Bank Branch</th>
                        <th>Account Number</th>
                        <th>IFSC Code</th>
                        <th>Accounts Head</th>
                        <th>Principal</th>
                        <th>Paid On</th>
                        <th>Remarks</th>
                        <th>Document</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data['data'] as $row)
                        <tr>
                            <td>{{ $row->voucher_no }}</td>
                            <td>{{ $row->request_no }}</td>
                            <td>{{ $row->section_name }}</td>
                            <td>{{ $row->semester_name }}</td>
                            <td>{{ $row->division_name }}</td>
                            <td>{{ $row->student_name }}</td>
                            <td>{{ $row->enrollment_no }}</td>
                            <td>{{ $row->admission_year }}</td>
                            <td>{{ number_format($row->final_refund_amount, 2) }}</td>
                            <td>{{ $row->bank_name }}</td>
                            <td>{{ $row->bank_branch }}</td>
                            <td>{{ $row->bank_account_number }}</td>
                            <td>{{ $row->ifsc_code }}</td>
                            <td>{{ $row->accounts_head_name }}</td>
                            <td>{{ $row->principal_name }}</td>
                            <td>{{ $row->paid_date }}</td>
                            <td>{{ $row->payment_reference }}</td>
                            <td>
                                @if(!empty($row->supporting_document))
                                    <a href="https://s3-triz.fra1.digitaloceanspaces.com/public/deposit_refund_docs/{{ $row->supporting_document }}" target="_blank" class="btn btn-xs btn-info">
                                        <i class="fa fa-file"></i> View
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="8" class="text-right">Total Refunded</th>
                        <th>{{ number_format($data['total_refunded'], 2) }}</th>
                        <th colspan="8"></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif
    </div>
    @include('includes.footerJs')
    @if (!empty($data['searched']))
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
                    var total = api.column(8, {page: 'current'}).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                    $(api.column(8).footer()).html(total.toFixed(2));
                    // columns 9-15 are bank/remarks/etc — no footer sum needed
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
    @endif
    @include('includes.footer')
</div>
