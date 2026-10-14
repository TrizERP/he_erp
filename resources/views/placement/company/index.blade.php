@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">Companies</h4>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                @if(App\Services\Placement\PlacementAccess::allows(['tpo']))
                    <a href="{{ route('placement.companies.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add Company</a>
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

        @if($data['follow_ups']->count())
            <div class="card">
                <h5>Follow-ups due (next 7 days)</h5>
                <ul class="list-unstyled" style="margin:0">
                    @foreach($data['follow_ups'] as $f)
                        <li>
                            <span class="label {{ $f->follow_up_date->isPast() ? 'label-danger' : 'label-warning' }}">{{ $f->follow_up_date->format('d-m-Y') }}</span>
                            <a href="{{ route('placement.companies.show', $f->company_id) }}">{{ $f->company->name ?? '' }}</a>
                            &mdash; {{ \Illuminate\Support\Str::limit($f->subject ?: $f->notes, 80) }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

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
                <div class="col-md-3 form-group">
                    <label>Industry</label>
                    <select id="f-industry" class="form-control">
                        <option value="">All</option>
                        @foreach($data['industries'] as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="pl-companies" class="table table-striped" style="width:100%">
                    <thead>
                    <tr>
                        <th>#</th><th>Company</th><th>Industry</th><th>HQ</th><th>MoU</th><th>Rating</th><th>Status</th><th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            var table = $('#pl-companies').DataTable({
                processing: true, serverSide: true,
                ajax: {
                    url: "{{ route('placement.companies.data') }}",
                    data: function (d) { d.status = $('#f-status').val(); d.industry_id = $('#f-industry').val(); }
                },
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name', name: 'placement_company.name'},
                    {data: 'industry_name', name: 'industry_name'},
                    {data: 'hq_location', name: 'placement_company.hq_location', defaultContent: ''},
                    {data: 'mou_status', name: 'placement_company.mou_status'},
                    {data: 'rating', name: 'placement_company.rating'},
                    {data: 'status', name: 'placement_company.status'},
                    {data: 'action', orderable: false, searchable: false}
                ]
            });
            $('#f-status, #f-industry').on('change', function () { table.ajax.reload(); });
        });
    </script>
    @include('includes.footer')
</div>
