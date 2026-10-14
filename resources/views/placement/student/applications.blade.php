@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">My Applications</h4>
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

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Company</th><th>Drive</th><th>Role</th><th>Applied</th><th>Status</th><th>Last update</th><th></th></tr></thead>
                    <tbody>
                    @forelse($data['applications'] as $a)
                        @php($color = ['applied' => 'info', 'shortlisted' => 'success', 'rejected' => 'danger', 'withdrawn' => 'default', 'selected' => 'success'][$a->status] ?? 'default')
                        <tr>
                            <td>{{ $a->drive->company->name ?? '' }}</td>
                            <td><a href="{{ route('placement.student.drives') }}">{{ $a->drive->title ?? '' }}</a></td>
                            <td>{{ $a->role->title ?? '' }}</td>
                            <td>{{ $a->applied_at?->format('d-m-Y') }}</td>
                            <td><span class="label label-{{ $color }}">{{ $data['statuses'][$a->status] ?? $a->status }}</span></td>
                            <td>{{ $a->status_changed_at?->format('d-m-Y H:i') }}</td>
                            <td>
                                @if(in_array($a->status, ['applied', 'shortlisted']) && ! in_array($a->drive->status ?? '', ['in_progress', 'completed', 'cancelled']))
                                    <form method="post" action="{{ route('placement.student.withdraw', $a->id) }}" onsubmit="return confirm('Withdraw this application?')">
                                        @csrf <button class="btn btn-xs btn-danger">Withdraw</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">You have not applied to any drive yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    @include('includes.footer')
</div>
