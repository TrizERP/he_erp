@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    @include('admission.process._style')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12"><h4 class="page-title">Admission Reports</h4></div>
        </div>

        @include('admission.process._flash')

        <p class="adm-intro">Choose a report, optionally narrow it by programme or dates, then press <b>Run</b>. Use <b>Export to Excel</b> to download exactly what you see.</p>

        <div class="card">
            <form method="GET" class="row">
                <div class="col-md-4 form-group">
                    <label>Report</label>
                    <select name="type" class="form-control" required>
                        <option value="">Choose...</option>
                        @foreach ($data['types'] as $key => $label)<option value="{{ $key }}" @selected($data['type'] === $key)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Programme</label>
                    <select name="standard_id" class="form-control">
                        <option value="">All</option>
                        @foreach ($data['standards'] as $standard)<option value="{{ $standard->id }}" @selected(($data['filters']['standard_id'] ?? '') == $standard->id)>{{ $standard->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group"><label>From</label><input type="date" name="from" class="form-control" value="{{ $data['filters']['from'] ?? '' }}"></div>
                <div class="col-md-2 form-group"><label>To</label><input type="date" name="to" class="form-control" value="{{ $data['filters']['to'] ?? '' }}"></div>
                <div class="col-md-1 form-group"><label>&nbsp;</label><br><button class="btn btn-success">Run</button></div>
            </form>
        </div>

        @if ($data['report'])
            <div class="card">
                <div class="row">
                    <div class="col-md-8"><h4>{{ $data['report']['title'] }} <small>({{ count($data['report']['rows']) }} row(s))</small></h4></div>
                    <div class="col-md-4 text-right">
                        <a class="btn btn-info" href="{{ route('admission.process.report_export', array_merge(['type' => $data['type']], $data['filters'])) }}">Export to Excel</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover adm-table">
                        <thead><tr>@foreach ($data['report']['headings'] as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead>
                        <tbody>
                        @forelse ($data['report']['rows'] as $row)
                            <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
                        @empty
                            <tr><td colspan="{{ count($data['report']['headings']) }}" class="text-center text-muted">No data for these filters.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
