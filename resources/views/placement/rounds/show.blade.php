@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php
    $r = $data['round'];
    $role = App\Services\Placement\PlacementAccess::role();
    $canWrite = App\Services\Placement\PlacementAccess::allows(['tpo']);
    $canAct = App\Services\Placement\PlacementAccess::allows(['tpo', 'coordinator']);
    $locked = $r->isPublished();
@endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">{{ $r->name }} @if($r->is_final)<span class="label label-primary">Final</span>@endif
                    <span class="label label-{{ $locked ? 'success' : 'default' }}">{{ $locked ? 'Results published' : ucfirst($r->status) }}</span></h4>
                <small>{{ $r->drive->title }} &middot; {{ $r->drive->company->name ?? '' }} &middot; {{ $r->round_date?->format('d-m-Y') }} {{ $r->venue }}</small>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                <a href="{{ route('placement.rounds.index', $r->drive_id) }}" class="btn btn-default">All rounds</a>
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

        <div class="row">
            <div class="col-md-3 col-xs-4"><div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $data['summary']['participants'] }}</h3><small>Participants</small></div></div>
            <div class="col-md-3 col-xs-4"><div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $data['summary']['present'] }}</h3><small>Present</small></div></div>
            <div class="col-md-3 col-xs-4"><div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $data['summary']['cleared'] }}</h3><small>Cleared</small></div></div>
            <div class="col-md-3 col-xs-12 text-right" style="padding-top:10px">
                @if($canWrite && ! $locked)
                    <form method="post" action="{{ route('placement.rounds.seed', $r->id) }}" style="display:inline">@csrf<button class="btn btn-default btn-sm">Load participants</button></form>
                    <form method="post" action="{{ route('placement.rounds.notify', $r->id) }}" style="display:inline">@csrf<button class="btn btn-default btn-sm">Notify schedule</button></form>
                @endif
                @if(! $locked)<button class="btn btn-info btn-sm" id="show-qr"><i class="fa fa-qrcode"></i> Attendance QR</button>@endif
            </div>
        </div>

        <div class="card" id="qr-card" style="display:none">
            <div class="text-center">
                <div id="qr"></div>
                <p class="text-muted" style="margin-top:8px">Students scan this with their phone while logged in to the ERP. It only works on the round date.</p>
                <code style="word-break:break-all">{{ $data['qr_url'] }}</code>
            </div>
        </div>

        <form method="post" action="{{ route('placement.rounds.save', $r->id) }}">
            @csrf
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Enrollment</th><th>Student</th><th>Course</th><th>Attendance</th><th>Result</th><th>Score</th><th>Remarks</th><th>Slot</th></tr></thead>
                        <tbody>
                        @forelse($data['rows'] as $row)
                            <tr>
                                <td>{{ $row->enrollment_no }}</td>
                                <td>{{ $row->student }}</td>
                                <td>{{ $row->course }}</td>
                                <td>
                                    <select name="rows[{{ $row->application_id }}][attendance]" class="form-control input-sm" @disabled($locked || ! $canAct)>
                                        @foreach($data['attendance'] as $k => $l)<option value="{{ $k }}" @selected($row->attendance == $k)>{{ $l }}</option>@endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="rows[{{ $row->application_id }}][result]" class="form-control input-sm" @disabled($locked || ! $canWrite)>
                                        @foreach($data['results'] as $k => $l)<option value="{{ $k }}" @selected($row->result == $k)>{{ $l }}</option>@endforeach
                                    </select>
                                </td>
                                <td style="width:90px"><input type="number" step="0.01" name="rows[{{ $row->application_id }}][score]" class="form-control input-sm" value="{{ $row->score }}" @disabled($locked || ! $canWrite)></td>
                                <td><input name="rows[{{ $row->application_id }}][remarks]" class="form-control input-sm" value="{{ $row->remarks }}" @disabled($locked || ! $canWrite)></td>
                                <td>{{ $row->slot_start ? \Carbon\Carbon::parse($row->slot_start)->format('d-m H:i').' ('.($row->panel ?: '-').')' : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No participants loaded yet.@if($canWrite) Click "Load participants".@endif</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($canAct && ! $locked && $data['rows']->count())
                    <button class="btn btn-primary btn-sm">Save {{ $canWrite ? 'attendance &amp; results' : 'attendance' }}</button>
                @endif
            </div>
        </form>

        @if($canWrite && ! $locked && $data['rows']->count())
            <div class="card">
                <form method="post" action="{{ route('placement.rounds.publish', $r->id) }}" onsubmit="return confirm('Publish results? Students will be notified and cannot be changed afterwards.')">
                    @csrf
                    <button class="btn btn-success"><i class="fa fa-bullhorn"></i> Publish results</button>
                    <span class="text-muted">Every participant needs a result first.@if($r->is_final) Clearing the final round marks students Selected.@endif</span>
                </form>
            </div>

            <div class="card">
                <h5>Interview slots</h5>
                <form method="post" action="{{ route('placement.rounds.slots.store', $r->id) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <select name="application_id" class="form-control" required>
                                <option value="">Student *</option>
                                @foreach($data['rows'] as $row)<option value="{{ $row->application_id }}">{{ $row->enrollment_no }} - {{ $row->student }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group"><input type="datetime-local" name="slot_start" class="form-control" required></div>
                        <div class="col-md-3 form-group"><input type="datetime-local" name="slot_end" class="form-control" required></div>
                        <div class="col-md-2 form-group"><input name="panel" class="form-control" placeholder="Panel"></div>
                        <div class="col-md-1"><button class="btn btn-success btn-sm">Save</button></div>
                    </div>
                </form>
                <form method="post" action="{{ route('placement.rounds.slots.auto', $r->id) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group"><label>Auto-schedule from</label><input type="datetime-local" name="first_start" class="form-control" required></div>
                        <div class="col-md-2 form-group"><label>Minutes each</label><input type="number" name="minutes" value="20" min="5" max="240" class="form-control" required></div>
                        <div class="col-md-5 form-group"><label>Panels (comma separated)</label><input name="panels" value="Panel 1, Panel 2" class="form-control" required></div>
                        <div class="col-md-2" style="padding-top:25px"><button class="btn btn-default btn-sm">Auto-schedule</button></div>
                    </div>
                </form>
                <small class="text-muted">Conflicts are rejected: a student cannot hold two overlapping slots and a panel cannot be double-booked.</small>
            </div>
        @endif
    </div>

    @include('includes.footerJs')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#show-qr').on('click', function () {
                $('#qr-card').toggle();
                if (!$('#qr canvas, #qr img').length) {
                    new QRCode(document.getElementById('qr'), {text: @json($data['qr_url']), width: 220, height: 220});
                }
            });
        });
    </script>
    @include('includes.footer')
</div>
