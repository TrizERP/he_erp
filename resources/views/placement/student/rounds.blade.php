@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title"><div class="col-lg-12"><h4 class="page-title">My Rounds &amp; Results</h4></div></div>

        @if ($sessionData = Session::get('data'))
            <div class="alert {{ $sessionData['status_code'] == 1 ? 'alert-success' : 'alert-danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $sessionData['message'] }}</strong>
            </div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Company</th><th>Round</th><th>Date</th><th>Venue</th><th>My slot</th><th>Attendance</th><th>Result</th></tr></thead>
                    <tbody>
                    @forelse($data['rounds'] as $row)
                        @php($r = $row['round'])
                        <tr>
                            <td>{{ $r->drive->company->name ?? '' }}<br><small class="text-muted">{{ $r->drive->title ?? '' }}</small></td>
                            <td>{{ $r->name }}</td>
                            <td>{{ $r->round_date?->format('d-m-Y') }} {{ $r->start_time ? substr($r->start_time, 0, 5) : '' }}</td>
                            <td>{{ $r->venue ?: '-' }}</td>
                            <td>{{ $row['slot'] ? $row['slot']->slot_start->format('d-m H:i').' - '.$row['slot']->slot_end->format('H:i').($row['slot']->panel ? ' ('.$row['slot']->panel.')' : '') : '-' }}</td>
                            <td>{{ ucfirst($row['attendance']) }}</td>
                            <td>
                                @if($row['result'])
                                    <span class="label label-{{ ['cleared' => 'success', 'rejected' => 'danger', 'on_hold' => 'warning'][$row['result']] ?? 'default' }}">{{ App\Models\placement\PlacementRoundResult::RESULTS[$row['result']] }}</span>
                                    @if($row['score'] !== null) <small>score {{ $row['score'] }}</small>@endif
                                    @if($row['remarks'])<br><small class="text-muted">{{ $row['remarks'] }}</small>@endif
                                @else
                                    <span class="text-muted">Awaiting</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">You are not part of any round yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
