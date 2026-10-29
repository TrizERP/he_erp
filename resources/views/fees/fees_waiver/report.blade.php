@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Fees Waiver Report</h4>
            </div>
        </div>
        <div class="card">
            <form action="{{ route('fees_waiver_report.index') }}" method="get" class="row">
                <div class="col-md-3 form-group">
                    <label>Student (name / enrollment no / mobile)</label>
                    <input type="text" name="search" value="{{ $data['filters']['search'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-2 form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="pending" @selected(($data['filters']['status'] ?? '') === 'pending')>Pending</option>
                        <option value="applied" @selected(($data['filters']['status'] ?? '') === 'applied')>Applied</option>
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label>From Date</label>
                    <input type="date" name="from_date" value="{{ $data['filters']['from_date'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-2 form-group">
                    <label>To Date</label>
                    <input type="date" name="to_date" value="{{ $data['filters']['to_date'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-3 form-group" style="margin-top:25px;">
                    <button type="submit" class="btn btn-success">Show</button>
                </div>
            </form>

            <div class="table-responsive">
                <table id="example" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Enrollment No</th>
                            <th>Student</th>
                            <th>Semester</th>
                            <th>Student Quota</th>
                            <th class="text-right">Fees Waiver</th>
                            <th>Remarks</th>
                            <th>Applied Date</th>
                            <th>Created By</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data['rows'] as $i => $r)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $r->enrollment_no }}</td>
                            <td>{{ trim($r->last_name.' '.$r->first_name.' '.$r->middle_name) }}</td>
                            <td>{{ $r->standard_name }} {{ $r->division_name }}</td>
                            <td>{{ $r->quota_name }}</td>
                            <td class="text-right">{{ number_format($r->amount, 2) }}</td>
                            <td>{{ $r->remarks }}</td>
                            <td>{{ $r->applied_at ? \Carbon\Carbon::parse($r->applied_at)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $r->created_by_name }}</td>
                            <td>{{ $r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('d-m-Y h:i A') : '' }}</td>
                        </tr>
                    @empty
                    @endforelse
                    </tbody>
                    @if(count($data['rows']))
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Total</th>
                            <th class="text-right">{{ number_format($data['total'], 2) }}</th>
                            <th colspan="4"></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
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
                    title: 'Fees Waiver Report',
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
                    title: 'Fees Waiver Report',
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
                    title: 'Fees Waiver Report',
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
                    title: 'Fees Waiver Report',
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
                var total = api.column(5, {page: 'current'}).data().reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
                $(api.column(5).footer()).html(total.toFixed(2));
            }
        });

        $('#example thead tr').clone(true).appendTo('#example thead');
        $('#example thead tr:eq(1) th').each(function (i) {
            var title = $(this).text();
            if (title == '#') {
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
