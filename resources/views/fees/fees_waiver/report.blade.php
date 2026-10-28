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
                    <button type="submit" name="export" value="csv" class="btn btn-info">Export CSV</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
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
                            <td class="text-right">{{ preg_replace('/\.00$/', '', number_format($r->amount, 2)) }}</td>
                            <td>{{ $r->remarks }}</td>
                            <td>{{ $r->applied_at ? \Carbon\Carbon::parse($r->applied_at)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $r->created_by_name }}</td>
                            <td>{{ $r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('d-m-Y h:i A') : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center">No fees waiver found</td></tr>
                    @endforelse
                    </tbody>
                    @if(count($data['rows']))
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Total</th>
                            <th class="text-right">{{ preg_replace('/\.00$/', '', number_format($data['total'], 2)) }}</th>
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
@include('includes.footer')
