@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php $canWrite = App\Services\Placement\PlacementAccess::allows(['tpo']); @endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12"><h4 class="page-title">Placement Settings &amp; Audit Log</h4></div>
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
            <h5>Notifications</h5>
            <form method="post" action="{{ route('placement.settings.update') }}">
                @csrf
                @foreach($data['labels'] as $key => $label)
                    @if(str_starts_with($key, 'notify_'))
                        <div class="checkbox"><label><input type="checkbox" name="{{ $key }}" value="1" @checked($data['values'][$key] == '1') @disabled(! $canWrite)> {{ $label }}</label></div>
                    @else
                        <div class="form-group" style="max-width:300px"><label>{{ $label }}</label>
                            <input type="number" name="{{ $key }}" min="1" max="168" class="form-control" value="{{ $data['values'][$key] }}" @disabled(! $canWrite)></div>
                    @endif
                @endforeach
                @if($canWrite)<button class="btn btn-success btn-sm">Save settings</button>@endif
            </form>
        </div>

        <div class="card">
            <h5>Audit log <small class="text-muted">offer changes, policy overrides, deletions and approvals</small></h5>
            <div class="row"><div class="col-md-3 form-group">
                <select id="f-entity" class="form-control"><option value="">All records</option>@foreach($data['entities'] as $e)<option value="{{ $e }}">{{ $e }}</option>@endforeach</select>
            </div></div>
            <div class="table-responsive">
                <table id="pl-audit" class="table table-striped" style="width:100%">
                    <thead><tr><th>#</th><th>When</th><th>Record</th><th>Id</th><th>Action</th><th>By</th><th>Reason</th><th>Before</th><th>After</th></tr></thead>
                </table>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            var table = $('#pl-audit').DataTable({
                processing: true, serverSide: true, order: [[1, 'desc']],
                ajax: {url: "{{ route('placement.settings.audit') }}", data: function (d) { d.entity = $('#f-entity').val(); }},
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'created_at', name: 'a.created_at'},
                    {data: 'entity_type', name: 'a.entity_type'},
                    {data: 'entity_id', name: 'a.entity_id', defaultContent: ''},
                    {data: 'action', name: 'a.action'},
                    {data: 'user_name', name: 'user_name', orderable: false, searchable: false},
                    {data: 'reason', name: 'a.reason', defaultContent: ''},
                    {data: 'old_values', name: 'a.old_values', defaultContent: ''},
                    {data: 'new_values', name: 'a.new_values', defaultContent: ''}
                ]
            });
            $('#f-entity').on('change', function () { table.ajax.reload(); });
        });
    </script>
    @include('includes.footer')
</div>
