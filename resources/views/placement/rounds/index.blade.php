@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php $d = $data['drive']; $canWrite = App\Services\Placement\PlacementAccess::allows(['tpo']); @endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">Rounds &mdash; {{ $d->title }}</h4>
                <small>{{ $d->company->name ?? '' }}</small>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                <a href="{{ route('placement.drives.show', $d->id) }}" class="btn btn-default">Back to drive</a>
            </div>
        </div>

        @if ($sessionData = Session::get('data'))
            <div class="alert {{ $sessionData['status_code'] == 1 ? 'alert-success' : 'alert-danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $sessionData['message'] }}</strong>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger"><ul style="margin:0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>#</th><th>Round</th><th>Mode</th><th>Date</th><th>Venue</th><th>Participants</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse($data['rounds'] as $r)
                        <tr>
                            <td>{{ $r->sequence }}</td>
                            <td>{{ $r->name }} @if($r->is_final)<span class="label label-primary">Final</span>@endif</td>
                            <td>{{ ucfirst($r->mode) }}</td>
                            <td>{{ $r->round_date?->format('d-m-Y') }} {{ $r->start_time ? substr($r->start_time, 0, 5) : '' }}</td>
                            <td>{{ $r->venue ?: '-' }}</td>
                            <td>{{ $r->results_count }}</td>
                            <td><span class="label label-{{ $r->isPublished() ? 'success' : 'default' }}">{{ $r->isPublished() ? 'Results published' : ucfirst($r->status) }}</span></td>
                            <td><a class="btn btn-xs btn-info" href="{{ route('placement.rounds.show', $r->id) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No rounds yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($canWrite && ! in_array($d->status, ['draft', 'completed', 'cancelled']))
            <div class="card">
                <h5>Add a round</h5>
                <form method="post" action="{{ route('placement.rounds.store', $d->id) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group"><input name="name" class="form-control" placeholder="Round name *" required></div>
                        <div class="col-md-2 form-group">
                            <select name="round_type_id" class="form-control"><option value="">Type</option>@foreach($data['round_types'] as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select>
                        </div>
                        <div class="col-md-1 form-group"><select name="mode" class="form-control"><option value="offline">Offline</option><option value="online">Online</option></select></div>
                        <div class="col-md-2 form-group"><input type="date" name="round_date" class="form-control"></div>
                        <div class="col-md-1 form-group"><input type="time" name="start_time" class="form-control"></div>
                        <div class="col-md-1 form-group"><input type="time" name="end_time" class="form-control"></div>
                        <div class="col-md-2 form-group"><input name="venue" class="form-control" placeholder="Venue / link"></div>
                        <div class="col-md-12"><label class="checkbox-inline"><input type="checkbox" name="is_final" value="1"> Final round (clearing it selects the student)</label>
                            &nbsp; <button class="btn btn-success btn-sm">Add round</button></div>
                    </div>
                </form>
            </div>
        @endif
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
