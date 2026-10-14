@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12"><h4 class="page-title">Training, Mock Tests &amp; Mock Interviews</h4></div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                @if(App\Services\Placement\PlacementAccess::allows(['tpo']))
                    <a href="{{ route('placement.training.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> New session</a>
                @endif
            </div>
        </div>

        @if ($sessionData = Session::get('data'))
            <div class="alert {{ $sessionData['status_code'] == 1 ? 'alert-success' : 'alert-danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $sessionData['message'] }}</strong>
            </div>
        @endif

        <div class="card">
            <div class="row"><div class="col-md-3 form-group">
                <label>Type</label>
                <select id="f-type" class="form-control"><option value="">All</option>@foreach($data['types'] as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
            </div></div>
            <div class="table-responsive">
                <table id="pl-training" class="table table-striped" style="width:100%">
                    <thead><tr><th>#</th><th>Session</th><th>Type</th><th>Trainer</th><th>Date</th><th>Participants</th><th>Present</th><th>Status</th><th></th></tr></thead>
                </table>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            var table = $('#pl-training').DataTable({
                processing: true, serverSide: true, order: [[4, 'desc']],
                ajax: {url: "{{ route('placement.training.data') }}", data: function (d) { d.training_type = $('#f-type').val(); }},
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'title', name: 't.title'},
                    {data: 'training_type', name: 't.training_type'},
                    {data: 'trainer', name: 't.trainer', defaultContent: ''},
                    {data: 'session_date', name: 't.session_date'},
                    {data: 'participants', searchable: false, orderable: false},
                    {data: 'present', searchable: false, orderable: false},
                    {data: 'status', name: 't.status'},
                    {data: 'action', orderable: false, searchable: false}
                ]
            });
            $('#f-type').on('change', function () { table.ajax.reload(); });
        });
    </script>
    @include('includes.footer')
</div>
