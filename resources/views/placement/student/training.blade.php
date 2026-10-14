@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php $rd = $data['readiness']; @endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title"><div class="col-lg-12"><h4 class="page-title">My Training &amp; Readiness</h4></div></div>

        @if ($sessionData = Session::get('data'))
            <div class="alert {{ $sessionData['status_code'] == 1 ? 'alert-success' : 'alert-danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button><strong>{{ $sessionData['message'] }}</strong>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger"><ul style="margin:0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <h5>Readiness score</h5>
                    @if($rd)
                        <h2 style="margin:0">{{ $rd['score'] }}<small>/100</small></h2>
                        <div class="progress" style="margin-top:8px"><div class="progress-bar progress-bar-{{ $rd['score'] >= 70 ? 'success' : ($rd['score'] >= 45 ? 'warning' : 'danger') }}" style="width: {{ $rd['score'] }}%"></div></div>
                        <small class="text-muted">
                            @foreach(['tests' => 'Tests', 'interviews' => 'Mock interviews', 'attendance' => 'Attendance', 'profile' => 'Profile'] as $k => $l)
                                @if(isset($rd['components'][$k]))<div>{{ $l }}: {{ $rd['components'][$k] }}%</div>@endif
                            @endforeach
                        </small>
                    @else
                        <p class="text-muted">Attend sessions and complete tests to build your score.</p>
                    @endif
                </div>
                <div class="card">
                    <h5>Resume review</h5>
                    <form method="post" action="{{ route('placement.student.review') }}">
                        @csrf
                        <div class="form-group"><textarea name="request_note" class="form-control" rows="2" placeholder="Anything specific you want reviewed? (optional)"></textarea></div>
                        <button class="btn btn-info btn-sm">Request a review</button>
                    </form>
                    @foreach($data['reviews'] as $rv)
                        <hr style="margin:10px 0">
                        <small class="text-muted">{{ $rv->requested_at->format('d-m-Y') }} &middot; {{ ucfirst($rv->status) }}</small>
                        @if($rv->feedback)<p>{!! nl2br(e($rv->feedback)) !!}</p>@endif
                    @endforeach
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <h5>My sessions</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Date</th><th>Session</th><th>Type</th><th>Attendance</th><th>Result</th><th>Feedback</th></tr></thead>
                            <tbody>
                            @forelse($data['sessions'] as $t)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($t->session_date)->format('d-m-Y') }} {{ $t->start_time ? substr($t->start_time, 0, 5) : '' }}</td>
                                    <td>{{ $t->title }}<br><small class="text-muted">{{ $t->venue }}</small></td>
                                    <td>{{ $data['types'][$t->training_type] ?? $t->training_type }}</td>
                                    <td>{{ ucfirst($t->attendance) }}</td>
                                    <td>@if($t->rating !== null){{ $t->rating }}/5 @elseif($t->score !== null){{ $t->score }}{{ $t->max_score ? ' / '.$t->max_score : '' }}@else - @endif</td>
                                    <td>{{ $t->feedback }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">No sessions assigned yet.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
