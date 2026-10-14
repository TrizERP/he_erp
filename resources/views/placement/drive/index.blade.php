@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">Job Drives</h4>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                @if(App\Services\Placement\PlacementAccess::allows(['tpo']))
                    <a href="{{ route('placement.drives.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> New Drive</a>
                @endif
            </div>
        </div>

        @if ($sessionData = Session::get('data'))
            <div class="alert {{ $sessionData['status_code'] == 1 ? 'alert-success' : 'alert-danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $sessionData['message'] }}</strong>
            </div>
        @endif

        <div class="row">
            @foreach($data['statuses'] as $key => $label)
                <div class="col-md-2 col-sm-4 col-xs-6">
                    <div class="card text-center" style="padding:10px">
                        <h3 style="margin:0">{{ $data['status_counts'][$key] ?? 0 }}</h3>
                        <small>{{ $label }}</small>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Status</label>
                    <select id="f-status" class="form-control">
                        <option value="">All</option>
                        @foreach($data['statuses'] as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="pl-drives" class="table table-striped" style="width:100%">
                    <thead>
                    <tr><th>#</th><th>Drive</th><th>Company</th><th>Mode</th><th>Date</th><th>Reg. deadline</th><th>Applicants</th><th>Status</th><th>Action</th></tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            var table = $('#pl-drives').DataTable({
                processing: true, serverSide: true,
                ajax: {url: "{{ route('placement.drives.data') }}", data: function (d) { d.status = $('#f-status').val(); }},
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'title', name: 'placement_drive.title'},
                    {data: 'company_name', name: 'company_name'},
                    {data: 'drive_mode', name: 'placement_drive.drive_mode'},
                    {data: 'drive_date', name: 'placement_drive.drive_date', defaultContent: ''},
                    {data: 'registration_deadline', name: 'placement_drive.registration_deadline', defaultContent: ''},
                    {data: 'applicants', searchable: false},
                    {data: 'status', name: 'placement_drive.status'},
                    {data: 'action', orderable: false, searchable: false}
                ]
            });
            $('#f-status').on('change', function () { table.ajax.reload(); });
        });
    </script>
    @include('includes.footer')
</div>
