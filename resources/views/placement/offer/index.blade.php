@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">Offers</h4>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                @if(App\Services\Placement\PlacementAccess::allows(['tpo']))
                    <a href="{{ route('placement.offers.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> Record offer</a>
                    <a href="{{ route('placement.offers.create', ['ppo' => 1]) }}" class="btn btn-info">PPO conversion</a>
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
                    <div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $data['status_counts'][$key] ?? 0 }}</h3><small>{{ $label }}</small></div>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="row"><div class="col-md-3 form-group">
                <label>Status</label>
                <select id="f-status" class="form-control"><option value="">All</option>@foreach($data['statuses'] as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
            </div></div>
            <div class="table-responsive">
                <table id="pl-offers" class="table table-striped" style="width:100%">
                    <thead><tr><th>#</th><th>Enrollment</th><th>Student</th><th>Company</th><th>Role</th><th>CTC (LPA)</th><th>Offered</th><th>Status</th><th></th></tr></thead>
                </table>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            var table = $('#pl-offers').DataTable({
                processing: true, serverSide: true,
                ajax: {url: "{{ route('placement.offers.data') }}", data: function (d) { d.status = $('#f-status').val(); }},
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'enrollment_no', name: 's.enrollment_no'},
                    {data: 'student', name: 'student', orderable: false, searchable: false},
                    {data: 'company', name: 'c.name'},
                    {data: 'role_title', name: 'o.role_title'},
                    {data: 'ctc_total', name: 'o.ctc_total'},
                    {data: 'offer_date', name: 'o.offer_date', defaultContent: ''},
                    {data: 'status', name: 'o.status'},
                    {data: 'action', orderable: false, searchable: false}
                ]
            });
            $('#f-status').on('change', function () { table.ajax.reload(); });
        });
    </script>
    @include('includes.footer')
</div>
