@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php
    $f = $data['filters'];
    $opt = $data['options'];
    $names = ['season_id' => 'Season', 'standard_id' => 'Course', 'drive_id' => 'Drive', 'company_id' => 'Company', 'status' => 'Status', 'placed' => 'Placement status'];
    $query = request()->query();
@endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
                <h4 class="page-title">{{ $data['def']['title'] }}</h4>
                <small>{{ $data['def']['description'] }}</small>
            </div>
            <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12 text-right">
                <a href="{{ route('placement.reports.index') }}" class="btn btn-default">All reports</a>
                <a href="{{ route('placement.reports.export', [$data['key'], 'excel'] + [] ) }}?{{ http_build_query($query) }}" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Excel</a>
                <a href="{{ route('placement.reports.export', [$data['key'], 'pdf']) }}?{{ http_build_query($query) }}" class="btn btn-danger"><i class="fa fa-file-pdf-o"></i> PDF</a>
            </div>
        </div>

        @if(count($data['def']['filters']))
            <div class="card">
                <form method="get" class="row">
                    @foreach($data['def']['filters'] as $name)
                        <div class="col-md-2 col-sm-4 form-group">
                            <label>{{ $names[$name] ?? ucwords(str_replace('_', ' ', $name)) }}</label>
                            @if(isset($opt[$name]))
                                <select name="{{ $name }}" class="form-control input-sm">
                                    <option value="">All</option>
                                    @foreach($opt[$name] as $v => $l)<option value="{{ $v }}" @selected(($f[$name] ?? '') == $v)>{{ $l }}</option>@endforeach
                                </select>
                            @elseif(in_array($name, ['from', 'to']))
                                <input type="date" name="{{ $name }}" class="form-control input-sm" value="{{ $f[$name] ?? '' }}">
                            @else
                                <input type="number" name="{{ $name }}" class="form-control input-sm" value="{{ $f[$name] ?? '' }}" placeholder="{{ $name === 'passing_year' ? 'e.g. 2026' : '' }}">
                            @endif
                        </div>
                    @endforeach
                    <div class="col-md-2 form-group" style="padding-top:25px"><button class="btn btn-primary btn-sm">Apply</button></div>
                </form>
            </div>
        @endif

        @if($data['key'] === 'eligibility' && empty($f['drive_id']))
            <div class="alert alert-info">Pick a drive to see its eligibility list.</div>
        @endif

        @if(count($data['summary']))
            <div class="row">
                @foreach($data['summary'] as $label => $value)
                    <div class="col-md-2 col-sm-4 col-xs-6"><div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $value }}</h3><small>{{ $label }}</small></div></div>
                @endforeach
            </div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table id="pl-report" class="table table-striped" style="width:100%">
                    <thead><tr><th>#</th>@foreach($data['columns'] as $label)<th>{{ $label }}</th>@endforeach</tr></thead>
                </table>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            var cols = [{data: 'DT_RowIndex', orderable: false, searchable: false}];
            @foreach(array_keys($data['columns']) as $key)
            cols.push({data: '{{ $key }}', name: '{{ $key }}', defaultContent: ''});
            @endforeach
            $('#pl-report').DataTable({
                processing: true, serverSide: {{ $data['server_side'] ? 'true' : 'false' }},
                ajax: "{{ route('placement.reports.data', $data['key']) }}?{{ http_build_query($query) }}",
                columns: cols, pageLength: 25
            });
        });
    </script>
    @include('includes.footer')
</div>
