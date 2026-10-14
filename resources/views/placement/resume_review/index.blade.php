@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title"><div class="col-lg-12"><h4 class="page-title">Resume Review Requests</h4></div></div>
        @if ($sessionData = Session::get('data'))
            <div class="alert {{ $sessionData['status_code'] == 1 ? 'alert-success' : 'alert-danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button><strong>{{ $sessionData['message'] }}</strong>
            </div>
        @endif
        <div class="row">
            @foreach(['requested' => 'Waiting', 'reviewed' => 'Reviewed'] as $k => $l)
                <div class="col-md-3 col-xs-6"><div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $data['counts'][$k] ?? 0 }}</h3><small>{{ $l }}</small></div></div>
            @endforeach
        </div>
        <div class="card">
            <div class="row"><div class="col-md-3 form-group">
                <select id="f-status" class="form-control"><option value="requested">Waiting</option><option value="reviewed">Reviewed</option><option value="">All</option></select>
            </div></div>
            <div class="table-responsive">
                <table id="pl-reviews" class="table table-striped" style="width:100%">
                    <thead><tr><th>#</th><th>Enrollment</th><th>Student</th><th>Course</th><th>Requested</th><th>Status</th><th></th></tr></thead>
                </table>
            </div>
        </div>
    </div>
    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            var table = $('#pl-reviews').DataTable({
                processing: true, serverSide: true,
                ajax: {url: "{{ route('placement.resume_reviews.data') }}", data: function (d) { d.status = $('#f-status').val(); }},
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'enrollment_no', name: 's.enrollment_no'},
                    {data: 'student', name: 'student', orderable: false, searchable: false},
                    {data: 'course', name: 'st.name'},
                    {data: 'requested_at', name: 'r.requested_at'},
                    {data: 'status', name: 'r.status'},
                    {data: 'action', orderable: false, searchable: false}
                ]
            });
            $('#f-status').on('change', function () { table.ajax.reload(); });
        });
    </script>
    @include('includes.footer')
</div>
