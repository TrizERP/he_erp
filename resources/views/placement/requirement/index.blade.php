@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title"><div class="col-lg-12"><h4 class="page-title">Recruiter Requirements</h4></div></div>
        @if ($sessionData = Session::get('data'))
            <div class="alert {{ $sessionData['status_code'] == 1 ? 'alert-success' : 'alert-danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button><strong>{{ $sessionData['message'] }}</strong>
            </div>
        @endif
        <div class="row">
            @foreach(['pending' => 'Pending', 'accepted' => 'Accepted', 'rejected' => 'Rejected'] as $k => $l)
                <div class="col-md-3 col-xs-4"><div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $data['counts'][$k] ?? 0 }}</h3><small>{{ $l }}</small></div></div>
            @endforeach
        </div>
        <div class="card">
            <div class="row"><div class="col-md-3 form-group">
                <select id="f-status" class="form-control"><option value="pending">Pending</option><option value="accepted">Accepted</option><option value="rejected">Rejected</option><option value="">All</option></select>
            </div></div>
            <div class="table-responsive">
                <table id="pl-req" class="table table-striped" style="width:100%">
                    <thead><tr><th>#</th><th>Company</th><th>Requirement</th><th>Openings</th><th>CTC</th><th>Skills</th><th>Contact</th><th>Status</th><th></th></tr></thead>
                </table>
            </div>
        </div>
    </div>
    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            var table = $('#pl-req').DataTable({
                processing: true, serverSide: true, order: [[0, 'desc']],
                ajax: {url: "{{ route('placement.requirements.data') }}", data: function (d) { d.status = $('#f-status').val(); }},
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'company', name: 'c.name'},
                    {data: 'title', name: 'q.title'},
                    {data: 'openings', name: 'q.openings', defaultContent: ''},
                    {data: 'ctc_range', name: 'q.ctc_range', defaultContent: ''},
                    {data: 'skills', name: 'q.skills', defaultContent: ''},
                    {data: 'contact_name', name: 'q.contact_name', defaultContent: ''},
                    {data: 'status', name: 'q.status'},
                    {data: 'action', orderable: false, searchable: false}
                ]
            });
            $('#f-status').on('change', function () { table.ajax.reload(); });
        });
    </script>
    @include('includes.footer')
</div>
