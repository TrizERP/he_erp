@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php $canWrite = App\Services\Placement\PlacementAccess::allows(['tpo']); @endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12"><h4 class="page-title">Communication &amp; Notification Log</h4></div>
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
            @foreach(['queued' => 'Queued', 'sent' => 'Sent', 'failed' => 'Failed', 'skipped' => 'Skipped'] as $k => $l)
                <div class="col-md-3 col-xs-6"><div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $data['counts'][$k] ?? 0 }}</h3><small>{{ $l }}</small></div></div>
            @endforeach
        </div>

        @if($canWrite)
            <div class="card">
                <h5>Send a message</h5>
                <form method="post" action="{{ route('placement.notifications.send') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group"><label>Audience *</label>
                            <select name="audience" id="audience" class="form-control" required>
                                <option value="registered">Registered students</option>
                                <option value="verified">Verified students</option>
                                <option value="drive_eligible">Drive: eligible students</option>
                                <option value="drive_applicants">Drive: applicants</option>
                                <option value="drive_shortlisted">Drive: shortlisted</option>
                            </select></div>
                        <div class="col-md-3 form-group" id="drive-box" style="display:none"><label>Drive</label>
                            <select name="drive_id" class="form-control"><option value="">-- Select --</option>@foreach($data['drives'] as $id => $t)<option value="{{ $id }}">{{ $t }}</option>@endforeach</select></div>
                        <div class="col-md-3 form-group"><label>Limit to courses (optional)</label>
                            <select name="standard_ids[]" class="form-control" multiple size="3">@foreach($data['standards'] as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>
                        <div class="col-md-12 form-group"><label>Templates to send * <small class="text-muted">(each goes through its own channel; placeholders {student_name} {company} {date} {venue} {deadline} are filled in)</small></label>
                            <div>
                                @forelse($data['templates'] as $t)
                                    <label class="checkbox-inline"><input type="checkbox" name="template_ids[]" value="{{ $t->id }}"> {{ strtoupper($t->channel) }}: {{ $t->name }}</label>
                                @empty
                                    <span class="text-muted">No active templates. Add some under Templates.</span>
                                @endforelse
                            </div></div>
                        <div class="col-md-12"><button class="btn btn-success btn-sm">Send</button></div>
                    </div>
                </form>
            </div>
        @endif

        <div class="card">
            <div class="row">
                <div class="col-md-2 form-group"><select id="f-channel" class="form-control"><option value="">All channels</option><option value="email">Email</option><option value="whatsapp">WhatsApp</option><option value="inapp">In-app</option></select></div>
                <div class="col-md-2 form-group"><select id="f-status" class="form-control"><option value="">All statuses</option><option value="queued">Queued</option><option value="sent">Sent</option><option value="failed">Failed</option><option value="skipped">Skipped</option></select></div>
            </div>
            <div class="table-responsive">
                <table id="pl-log" class="table table-striped" style="width:100%">
                    <thead><tr><th>#</th><th>When</th><th>Student</th><th>Channel</th><th>Event</th><th>To</th><th>Subject</th><th>Status</th><th>Error</th><th></th></tr></thead>
                </table>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            $('#audience').on('change', function () { $('#drive-box').toggle($(this).val().indexOf('drive_') === 0); });
            var table = $('#pl-log').DataTable({
                processing: true, serverSide: true, order: [[1, 'desc']],
                ajax: {url: "{{ route('placement.notifications.data') }}", data: function (d) { d.channel = $('#f-channel').val(); d.status = $('#f-status').val(); }},
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'created_at', name: 'n.created_at'},
                    {data: 'student', name: 'student', orderable: false, searchable: false, defaultContent: ''},
                    {data: 'channel', name: 'n.channel'},
                    {data: 'event_key', name: 'n.event_key'},
                    {data: 'recipient', name: 'n.recipient', defaultContent: ''},
                    {data: 'subject', name: 'n.subject', defaultContent: ''},
                    {data: 'status', name: 'n.status'},
                    {data: 'error', name: 'n.error', defaultContent: ''},
                    {data: 'action', orderable: false, searchable: false}
                ]
            });
            $('#f-channel, #f-status').on('change', function () { table.ajax.reload(); });
        });
    </script>
    @include('includes.footer')
</div>
