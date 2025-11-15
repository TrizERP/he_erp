@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Fees Status Report</h4>
            </div>
        </div>
        @php
            $grade_id = $standard_id = $division_id = $enrollment_no = $receipt_no = $from_date = $to_date = '';

            if (isset($data['grade_id'])) {
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            if (isset($data['enrollment_no'])) {
                $enrollment_no = $data['enrollment_no'];
            }
            if (isset($data['receipt_no'])) {
                $receipt_no = $data['receipt_no'];
            }
            if (isset($data['from_date'])) {
                $from_date = $data['from_date'];
            }
            if (isset($data['to_date'])) {
                $to_date = $data['to_date'];
            }
        @endphp


        <div class="card">
            @if ($sessionData = Session::get('data'))
                @if ($sessionData['status_code'] == 1)
                    <div class="alert alert-success alert-block">
                    @else
                        <div class="alert alert-danger alert-block">
                @endif
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $sessionData['message'] }}</strong>
        </div>
        @endif
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message }}</strong>
            </div>
        @endif
        <form action="{{ route('show_fees_status_report') }}" enctype="multipart/form-data" method="post">
            {{ method_field('POST') }}
            @csrf

            <div class="row">

                {{ App\Helpers\SearchChain('4', 'single', 'grade,std,div', $grade_id, $standard_id, $division_id) }}

                @if (isset($data['months']))
                    <div class="col-md-3 form-group">
                        <label>Months:</label>
                        <select name="month[]" class="form-control" required="required" multiple="multiple">
                            @foreach ($data['months'] as $key => $value)
                                <option value="{{ $key }}"
                                    @if (isset($data['month'])) @if (in_array($key, $data['month']))
                                                SELECTED @endif
                                    @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (isset($data['fees_heads']))
                    <div class="col-md-3 form-group">
                        <label>Fees Heads:</label>
                        <select name="fees_head[]" class="form-control" required="required" multiple="multiple">
                            @foreach ($data['fees_heads'] as $key => $value)
                                <option value="{{ $key }}"
                                    @if (isset($data['fees_head'])) @if (in_array($key, $data['fees_head']))
                                                SELECTED @endif
                                    @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                {{-- Fees Status Filter --}}
                <div class="col-md-3 form-group">
                    <label>Fees Status:</label>
                    <select class="form-control" name="fees_status">
                        <option value="">-Select-</option>
                        <option value="paid"
                            {{ isset($data['fees_status']) && $data['fees_status'] == 'paid' ? 'selected' : '' }}>Paid
                        </option>
                        <option value="unpaid"
                            {{ (isset($data['fees_status']) && $data['fees_status'] == 'unpaid') || !isset($data['fees_status']) ? 'selected' : '' }}>
                            Unpaid</option>
                    </select>
                </div>
                {{-- End Fees Status Filter --}}
                @if (!empty($data['number_types']) && is_array($data['number_types']))
                    <div class="col-md-3 form-group">
                        <label>Number Type:</label>
                        <select class="form-control" name="number_type">
                            <option value="">Select Number</option>
                            @foreach ($data['number_types'] as $key => $value)
                                <option value="{{ $key }}"
                                    @if (isset($data['number_type'])) @if ($key == $data['number_type'])
                                                SELECTED @endif
                                    @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-12 form-group">
                    <center>
                        <input type="submit" name="submit" value="Search" class="btn btn-success">
                    </center>
                </div>
            </div>

        </form>
    </div>
</div>

@if (isset($data['fees_data']))
    @php
        $isPaidMode = isset($data['fees_status']) && $data['fees_status'] == 'paid';
    @endphp
    @php
        if (isset($data['fees_data'])) {
            $fees_data = $data['fees_data'];
        }
    @endphp

    <div class="card">

        <div class="col-lg-12 col-sm-12 col-xs-12">
            <div class="table-responsive">

                @if ($isPaidMode)
                    {{-- ============================ --}}
                    {{--      PAID STUDENT TABLE     --}}
                    {{-- ============================ --}}

                    <table id="example" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Receipt No</th>
                                <th>Enrollment No</th>
                                <th>Student Name</th>
                                <th>Semester</th>
                                <th>Section</th>
                                <th>Quota</th>
                                <th>Father Mobile</th>
                                <th>Student Mobile</th>
                                <th>Mother Mobile</th>
                                <th>Total Amount Payable</th>
                                <th>Paid Amount</th>
                                <!--<th>Status</th>-->
                            </tr>
                        </thead>

                        <tbody>
                            @php $i=1; @endphp

                            @foreach ($fees_data as $sid => $value)
                                @php
                                    $paidTotal = $value['total_paid'] ?? 0;
                                    if ($paidTotal <= 0) {
                                        continue;
                                    }
                                @endphp

                                <tr>
                                    <td>{{ $i++ }}</td>

                                    <td>
                                        @foreach ($value['all_receipts'] as $rc)
                                            {{ $rc }}<br>
                                        @endforeach
                                    </td>

                                    <td>{{ $value['enrollment_no'] }}</td>
                                    <td>{{ $value['student_name'] }}</td>
                                    <td>{{ $value['standard_name'] }}</td>
                                    <td>{{ $value['division_name'] }}</td>
                                    <td>{{ $value['quota'] }}</td>

                                    <td>{{ $value['mobile'] }}</td>
                                    <td>{{ $value['student_mobile'] }}</td>
                                    <td>{{ $value['mother_mobile'] }}</td>
                                    <td>{{($value['total_payable']) }}</td>
                                    <td>{{ ($value['total_paid']) }}</td>

                                    <!--<td><span class="label label-success">PAID</span></td>-->
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    {{-- ============================ --}}
                    {{--   ORIGINAL UNPAID TABLE      --}}
                    {{-- ============================ --}}

                    <table id="example" class="table table-striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="fees_check_all" /></th>
                                <th>{{ App\Helpers\get_string('grno', 'request') }}</th>
                                <th>{{ App\Helpers\get_string('studentname', 'request') }}</th>
                                <th>{{ App\Helpers\get_string('standard', 'request') }}</th>
                                <th>{{ App\Helpers\get_string('division', 'request') }}</th>
                                <th>Quota</th>
                                @if ($data['number_type'] != '')
                                    <th>{{ $data['number_types'][$data['number_type']] ?? '-' }}</th>
                                @else
                                    @foreach ($data['number_types'] as $key => $value)
                                        <th>{{ $value }}</th>
                                    @endforeach
                                @endif

                                @foreach ($data['fees_head'] as $dk => $dv)
                                    <th>{{ $data['fees_heads'][$dv] }}</th>
                                @endforeach

                                <th>Previous Fees</th>
                                <th>Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php $j=1; @endphp
                            @foreach ($fees_data as $key => $value)
                                @php $amount = 0; @endphp

                                @foreach ($data['fees_head'] as $dk => $dv)
                                    @if (isset($data['fees_details'][$value['id']][$data['fees_heads'][$dv]]))
                                        @php $amount += $data['fees_details'][$value['id']][$data['fees_heads'][$dv]]; @endphp
                                    @endif
                                @endforeach

                                @if ($amount)
                                    <tr>
                                        @php
                                            $sendNumber = $value['mobile'] ?? '';
                                            if ($data['number_type'] != '') {
                                                $sendNumber = $value[$data['number_type']];
                                            }
                                        @endphp

                                        <td>
                                            <input type='checkbox' name="stu_ids[]" class="remain_fees"
                                                data-id="{{ $value['id'] }}" data-name="{{ $value['student_name'] }}"
                                                data-remain_fees="{{ $amount }}"
                                                data-mobile="{{ $sendNumber }}" />
                                        </td>

                                        <td>{{ $value['enrollment_no'] }}</td>
                                        <td>{{ $value['student_name'] }}</td>
                                        <td>{{ $value['standard_name'] }}</td>
                                        <td>{{ $value['division_name'] }}</td>
                                        <td>{{ $value['quota'] }}</td>

                                        @if ($data['number_type'] != '')
                                            <td>{{ $value[$data['number_type']] }}</td>
                                        @else
                                            @foreach ($data['number_types'] as $nkey => $nvalue)
                                                <td>{{ $value[$nkey] ?? '-' }}</td>
                                            @endforeach
                                        @endif

                                        @foreach ($data['fees_head'] as $dk => $dv)
                                            @if (isset($data['fees_details'][$value['id']][$data['fees_heads'][$dv]]))
                                                <td>{{ $data['fees_details'][$value['id']][$data['fees_heads'][$dv]] }}
                                                </td>
                                            @else
                                                <td>0</td>
                                            @endif
                                        @endforeach

                                        <td>{{ $data['previous_dues'][$value['id']] ?? 0 }}</td>
                                        <td>{{ $amount + ($data['previous_dues'][$value['id']] ?? 0) }}</td>
                                    </tr>
                                @endif

                            @endforeach
                        </tbody>
                    </table>

                @endif

            </div>
            <div class="col-md-12 form-group">
                <center>
                    <a href="javascript:void(0)" id="remain_fees_sms" class="btn btn-success">Sent SMS</a>
                </center>
            </div>
        </div>
    </div>

@endif

</div>

@include('includes.footerJs')
<script>
    (function() {
        if (window.__feesDTInit) return;
        window.__feesDTInit = true;

        $(function() {
            var $tbl = $('#example');

            // Destroy previous DT + cleanup
            if ($.fn.DataTable.isDataTable($tbl)) {
                $tbl.DataTable().destroy(true);
            }
            $tbl.find('thead tr').slice(1).remove();
            $tbl.find('tfoot').remove();

            // Build single filter row
            var $filter = $('<tr class="filters"></tr>');
            $tbl.find('thead tr:first th').each(function() {
                var title = $(this).text();
                $filter.append('<th><input type="text" placeholder="Search ' + title + '"/></th>');
            });
            $tbl.find('thead').append($filter);

            // Init DataTable
            var table = $tbl.DataTable({
                orderCellsTop: true,
                select: true,
                lengthMenu: [
                    [100, 500, 1000, -1],
                    ['100', '500', '1000', 'Show All']
                ],
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'pdfHtml5',
                        title: 'Fees Status Report',
                        orientation: 'landscape',
                        pageSize: 'A0',
                        exportOptions: {
                            columns: ':visible',
                            footer: true
                        }
                    },
                    {
                        extend: 'csv',
                        text: ' CSV',
                        title: 'Fees Status Report',
                        exportOptions: {
                            columns: ':visible',
                            footer: true
                        }
                    },
                    {
                        extend: 'excel',
                        text: ' EXCEL',
                        title: 'Fees Status Report',
                        exportOptions: {
                            columns: ':visible',
                            footer: true
                        }
                    },
                    {
                        extend: 'print',
                        text: ' PRINT',
                        title: 'Fees Status Report',
                        exportOptions: {
                            columns: ':visible',
                            footer: true
                        },
                        customize: function(win) {
                            $(win.document.body).prepend(`{!! App\Helpers\get_school_details("$grade_id", "$standard_id", "$division_id") !!}`);
                        }
                    },
                    'pageLength'
                ],
                order: [
                    [0, 'asc']
                ],

                // === FOOTER TOTAL LOGIC ===
                footerCallback: function() {
                    var api = this.api();
                    var $tbl = $('#example');

                    // 1) Headers in lowercase
                    var headers = [];
                    $tbl.find('thead tr:first th').each(function() {
                        headers.push($(this).text().trim().toLowerCase());
                    });

                    // 2) Fees head names from PHP (exact labels printed in the header)
                    //    This uses the same array you rendered in Blade.
                    const feeHeadNames = {!! json_encode(array_values(array_intersect_key($data['fees_heads'], array_flip($data['fees_head'] ?? [])))) !!}.map(s => (s || '').toString()
                        .trim().toLowerCase());

                    // 3) Indexes we care about
                    const prevIdx = headers.indexOf('previous fees');
                    const amtIdx = headers.indexOf('amount');

                    // 4) Helper to parse numbers like "16,000.00"
                    const parseNum = v => {
                        if (typeof v === 'string') v = v.replace(/,/g, '').trim();
                        const n = parseFloat(v);
                        return isNaN(n) ? 0 : n;
                    };

                    // 5) Make sure there is a tfoot with same number of cells
                    if (!$tbl.find('tfoot').length) {
                        let cells = '';
                        for (let i = 0; i < headers.length; i++) cells += '<th></th>';
                        $tbl.append('<tfoot><tr>' + cells + '</tr></tfoot>');
                    }
                    const $f = $tbl.find('tfoot tr th');

                    // 6) Clear old totals
                    $f.html('');

                    // 7) Fees Head: sum EACH selected fee head column and write under its column
                    let firstFeeIdx = null;
                    feeHeadNames.forEach(name => {
                        const idx = headers.indexOf(name);
                        if (idx > -1) {
                            if (firstFeeIdx === null) firstFeeIdx = idx;
                            const total = api.column(idx, {
                                    search: 'applied',
                                    page: 'all'
                                }).data()
                                .reduce((a, b) => parseNum(a) + parseNum(b), 0);
                            $f.eq(idx).html('<strong>' + total.toLocaleString(
                                undefined) + '</strong>');
                        }
                    });
                    if (firstFeeIdx !== null && firstFeeIdx - 1 >= 0) {
                        $f.eq(firstFeeIdx - 1).html('<strong>Total </strong>');
                    }

                    // 8) Previous Due total
                    if (prevIdx > -1) {
                        const prevTotal = api.column(prevIdx, {
                                search: 'applied',
                                page: 'all'
                            }).data()
                            .reduce((a, b) => parseNum(a) + parseNum(b), 0);
                        $f.eq(prevIdx).html('<strong>' + prevTotal.toLocaleString(undefined) +
                            '</strong>');
                    }

                    // 9) Amount total
                    if (amtIdx > -1) {
                        const amountTotal = api.column(amtIdx, {
                                search: 'applied',
                                page: 'all'
                            }).data()
                            .reduce((a, b) => parseNum(a) + parseNum(b), 0);
                        $f.eq(amtIdx).html('<strong>' + amountTotal.toLocaleString(undefined) +
                            '</strong>');
                    }
                }

            });

            // Filter inputs
            $tbl.find('thead tr.filters th input').each(function(i) {
                $(this).on('keyup change', function() {
                    if (table.column(i).search() !== this.value) {
                        table.column(i).search(this.value).draw();
                    }
                });
            });
        });
    })();
</script>

@include('includes.footer')
