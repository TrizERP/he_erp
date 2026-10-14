@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php
    $view = $data['view'];
    $k = $data['kpi'] ?? null;
    $a = $data['analytics'] ?? null;
    $st = $data['student'] ?? null;
    $canSwitch = App\Services\Placement\PlacementAccess::allows(['principal']) || App\Services\Placement\PlacementAccess::role() === 'tpo' || App\Services\Placement\PlacementAccess::role() === 'admin';
    $tile = fn ($value, $label, $class = '') => '<div class="col-md-2 col-sm-4 col-xs-6"><div class="card text-center '.$class.'" style="padding:12px"><h3 style="margin:0">'.e($value).'</h3><small>'.e($label).'</small></div></div>';
@endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <h4 class="page-title">Placement Dashboard</h4>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                @if($view !== 'student')
                    <form method="get" class="form-inline" style="display:inline">
                        @if($canSwitch)
                            <select name="view" class="form-control input-sm" onchange="this.form.submit()">
                                <option value="management" @selected($view === 'management')>Operations</option>
                                <option value="principal" @selected($view === 'principal')>Management analytics</option>
                            </select>
                        @endif
                        <select name="season_id" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="">All seasons</option>
                            @foreach($data['seasons'] as $id => $n)<option value="{{ $id }}" @selected($data['season_id'] == $id)>{{ $n }}</option>@endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>

        @if(in_array($view, ['management', 'coordinator']))
            <div class="row">
                {!! $tile($k['registered'], 'Registered students') !!}
                {!! $tile($k['verified'], 'Verified profiles') !!}
                {!! $tile($k['eligible'], 'Eligible students') !!}
                {!! $tile($k['placed'], 'Placed students') !!}
                {!! $tile($k['placed_percent'].'%', 'Placement %') !!}
                {!! $tile($k['offers_made'], 'Offers made') !!}
            </div>
            <div class="row">
                {!! $tile($k['ctc']['highest'], 'Highest CTC (LPA)') !!}
                {!! $tile($k['ctc']['average'], 'Average CTC (LPA)') !!}
                {!! $tile($k['ctc']['median'], 'Median CTC (LPA)') !!}
                {!! $tile($k['companies']['visited'].' / '.$k['companies']['invited'], 'Companies visited / invited') !!}
                {!! $tile($k['active_drives'], 'Active drives') !!}
                {!! $tile($k['pending_verification'].($view === 'management' ? ' / '.$k['pending_offer_verification'] : ''), $view === 'management' ? 'Pending profiles / offers' : 'Pending profiles', $k['pending_verification'] ? 'bg-warning' : '') !!}
            </div>

            @if($view === 'coordinator')
                <div class="row">
                    {!! $tile($data['actions']['not_registered'], 'Students not registered') !!}
                    {!! $tile($data['actions']['incomplete_profiles'], 'Profiles under 60% complete') !!}
                    {!! $tile($data['actions']['rejected_profiles'], 'Rejected profiles') !!}
                </div>
            @endif

            <div class="row">
                <div class="col-md-8">
                    <div class="card"><h5>Department-wise placed vs unplaced</h5><div id="chart-dept"></div></div>
                </div>
                <div class="col-md-4">
                    <div class="card"><h5>Upcoming drives &amp; rounds</h5>
                        @forelse($k['upcoming'] as $e)
                            <div style="padding:4px 0;border-bottom:1px solid #eee">
                                <span class="label label-{{ $e['type'] === 'Drive' ? 'info' : 'primary' }}">{{ $e['type'] }}</span>
                                <strong>{{ \Carbon\Carbon::parse($e['date'])->format('d M') }}</strong> {{ $e['time'] }}
                                <a href="{{ $e['url'] }}">{{ $e['title'] }}</a>
                            </div>
                        @empty
                            <p class="text-muted">Nothing scheduled.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="card">
                <h5>Department summary</h5>
                <div class="table-responsive"><table class="table table-striped">
                    <thead><tr><th>Course</th><th>Registered</th><th>Placed</th><th>Unplaced</th><th>%</th></tr></thead>
                    <tbody>@foreach($k['departments'] as $d)<tr><td>{{ $d['course'] }}</td><td>{{ $d['total'] }}</td><td>{{ $d['placed'] }}</td><td>{{ $d['unplaced'] }}</td><td>{{ $d['percent'] }}%</td></tr>@endforeach</tbody>
                </table></div>
            </div>
        @endif

        @if($view === 'principal')
            <div class="row">
                {!! $tile($a['ctc']['count'], 'Students placed') !!}
                {!! $tile($a['ctc']['highest'], 'Highest CTC (LPA)') !!}
                {!! $tile($a['ctc']['average'], 'Average CTC (LPA)') !!}
                {!! $tile($a['ctc']['median'], 'Median CTC (LPA)') !!}
            </div>
            <div class="row">
                <div class="col-md-8"><div class="card"><h5>Year-on-year trend</h5><div id="chart-trend"></div></div></div>
                <div class="col-md-4"><div class="card"><h5>Sector split</h5><div id="chart-sector"></div></div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="card"><h5>Package distribution</h5><div id="chart-dist"></div></div></div>
                <div class="col-md-6"><div class="card"><h5>Top recruiters</h5>
                    <div class="table-responsive"><table class="table table-striped">
                        <thead><tr><th>Company</th><th>Hires</th><th>Highest CTC</th></tr></thead>
                        <tbody>@forelse($a['top'] as $t)<tr><td>{{ $t['company'] }}</td><td>{{ $t['hires'] }}</td><td>{{ $t['highest'] }}</td></tr>@empty<tr><td colspan="3" class="text-muted text-center">No placements yet.</td></tr>@endforelse</tbody>
                    </table></div></div></div>
            </div>
        @endif

        @if($view === 'student')
            @php($p = $st['profile'])
            <div class="row">
                <div class="col-md-4"><div class="card"><h5>Profile completeness</h5>
                    <div class="progress"><div class="progress-bar progress-bar-success" style="width: {{ $p->completeness ?? 0 }}%">{{ $p->completeness ?? 0 }}%</div></div>
                    <p>Registration: <strong>{{ ucwords(str_replace('_', ' ', $p->registration_status ?? 'not_registered')) }}</strong> &middot; Verification: <strong>{{ ucfirst($p->verification_status ?? 'pending') }}</strong></p>
                    <a href="{{ route('placement.student.profile') }}" class="btn btn-info btn-sm">Open profile</a></div></div>
                <div class="col-md-2 col-xs-6"><div class="card text-center" style="padding:12px"><h3 style="margin:0">{{ $st['eligible_drives'] }}</h3><small><a href="{{ route('placement.student.drives') }}">Eligible drives</a></small></div></div>
                <div class="col-md-2 col-xs-6"><div class="card text-center" style="padding:12px"><h3 style="margin:0">{{ $st['unread'] }}</h3><small><a href="{{ route('placement.student.notices') }}">Unread notifications</a></small></div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="card"><h5>My applications</h5>
                    <div class="table-responsive"><table class="table table-condensed">
                        <tbody>@forelse($st['applications'] as $ap)<tr><td>{{ $ap->company }}<br><small class="text-muted">{{ $ap->role }}</small></td><td><span class="label label-{{ ['applied' => 'info', 'shortlisted' => 'success', 'rejected' => 'danger', 'withdrawn' => 'default', 'selected' => 'success'][$ap->status] ?? 'default' }}">{{ ucfirst($ap->status) }}</span></td><td><small>{{ \Carbon\Carbon::parse($ap->status_changed_at ?? $ap->applied_at)->format('d M') }}</small></td></tr>@empty<tr><td class="text-muted">No applications yet.</td></tr>@endforelse</tbody>
                    </table></div></div></div>
                <div class="col-md-6"><div class="card"><h5>Upcoming rounds</h5>
                    @forelse($st['rounds'] as $r)<div style="padding:4px 0;border-bottom:1px solid #eee"><strong>{{ \Carbon\Carbon::parse($r->round_date)->format('d M') }}</strong> {{ $r->start_time ? substr($r->start_time, 0, 5) : '' }} &middot; {{ $r->company }} - {{ $r->name }} <small class="text-muted">{{ $r->venue }}</small></div>@empty<p class="text-muted">No upcoming rounds.</p>@endforelse</div>
                    <div class="card"><h5>My offers</h5>
                    @forelse($st['offers'] as $o)<div style="padding:4px 0;border-bottom:1px solid #eee">{{ $o->company }} - {{ $o->role_title }} <strong>{{ $o->ctc_total }} LPA</strong> <span class="label label-info">{{ ucwords(str_replace('_', ' ', $o->status)) }}</span></div>@empty<p class="text-muted">No offers yet.</p>@endforelse
                    <a href="{{ route('placement.student.offers') }}" class="btn btn-default btn-xs">View offers</a></div></div>
            </div>
        @endif
    </div>

    @include('includes.footerJs')
    <script>
        window.addEventListener('load', function () {
            function chart(id, options) { var el = document.querySelector(id); if (el && window.ApexCharts) { new ApexCharts(el, options).render(); } }
            @if(in_array($view, ['management', 'coordinator']))
            chart('#chart-dept', {
                chart: {type: 'bar', height: 320, stacked: true, toolbar: {show: false}},
                series: [{name: 'Placed', data: @json($k['departments']->pluck('placed'))}, {name: 'Unplaced', data: @json($k['departments']->pluck('unplaced'))}],
                xaxis: {categories: @json($k['departments']->pluck('course'))},
                colors: ['#00c292', '#fb9678'], dataLabels: {enabled: false}
            });
            @endif
            @if($view === 'principal')
            chart('#chart-trend', {
                chart: {type: 'line', height: 320, toolbar: {show: false}},
                series: [{name: 'Placed', type: 'column', data: @json($a['trend']->pluck('placed'))}, {name: 'Median CTC', type: 'line', data: @json($a['trend']->pluck('median'))}, {name: 'Average CTC', type: 'line', data: @json($a['trend']->pluck('average'))}],
                xaxis: {categories: @json($a['trend']->pluck('season'))}, stroke: {width: [0, 3, 3]}
            });
            chart('#chart-sector', {chart: {type: 'donut', height: 320}, labels: @json($a['sectors']->keys()), series: @json($a['sectors']->values())});
            chart('#chart-dist', {
                chart: {type: 'bar', height: 300, toolbar: {show: false}},
                series: [{name: 'Students', data: @json(collect($a['distribution'])->pluck('count'))}],
                xaxis: {categories: @json(collect($a['distribution'])->pluck('label'))}, colors: ['#03a9f3'], dataLabels: {enabled: true}
            });
            @endif
        });
    </script>
    @include('includes.footer')
</div>
