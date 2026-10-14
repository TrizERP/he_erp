@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Student Profile Verification</h4>
            </div>
        </div>

        @if ($sessionData = Session::get('data'))
            <div class="alert {{ $sessionData['status_code'] == 1 ? 'alert-success' : 'alert-danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $sessionData['message'] }}</strong>
            </div>
        @endif

        <div class="row">
            @foreach(['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected'] as $key => $label)
                <div class="col-md-4 col-sm-4">
                    <div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $data['counts'][$key] ?? 0 }}</h3><small>{{ $label }}</small></div>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="row"><div class="col-md-3 form-group">
                <label>Status</label>
                <select id="f-status" class="form-control">
                    <option value="pending" selected>Pending</option><option value="verified">Verified</option><option value="rejected">Rejected</option><option value="">All</option>
                </select>
            </div></div>
            <div class="table-responsive">
                <table id="pl-verify" class="table table-striped" style="width:100%">
                    <thead><tr><th>#</th><th>Enrollment</th><th>Student</th><th>Course</th><th>CGPA</th><th>Backlogs</th><th>Complete</th><th>Status</th><th>Action</th></tr></thead>
                </table>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            var table = $('#pl-verify').DataTable({
                processing: true, serverSide: true,
                ajax: {url: "{{ route('placement.verification.data') }}", data: function (d) { d.status = $('#f-status').val(); }},
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'enrollment_no', name: 's.enrollment_no'},
                    {data: 'student', name: 'student', orderable: false, searchable: false},
                    {data: 'course', name: 'st.name'},
                    {data: 'cgpa', name: 'p.cgpa', defaultContent: ''},
                    {data: 'active_backlogs', name: 'p.active_backlogs'},
                    {data: 'completeness', name: 'p.completeness'},
                    {data: 'verification_status', name: 'p.verification_status'},
                    {data: 'action', orderable: false, searchable: false}
                ]
            });
            $('#f-status').on('change', function () { table.ajax.reload(); });
        });
    </script>
    @include('includes.footer')
</div>
