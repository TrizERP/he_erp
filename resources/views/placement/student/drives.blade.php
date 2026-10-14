@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php $p = $data['profile']; @endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Drives For Me</h4>
            </div>
        </div>

        @if ($sessionData = Session::get('data'))
            <div class="alert {{ $sessionData['status_code'] == 1 ? 'alert-success' : 'alert-danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $sessionData['message'] }}</strong>
            </div>
        @endif

        @unless($p->isActive())
            <div class="alert alert-warning">
                @if($p->registration_status !== 'registered')
                    Register for placements on <a href="{{ route('placement.student.profile') }}">your profile</a> to see drives.
                @else
                    Your profile is <strong>{{ $p->verification_status }}</strong>. Drives appear once it is verified.
                @endif
            </div>
        @endunless

        @forelse($data['drives'] as $item)
            @php($d = $item['drive'])
            <div class="card">
                <div class="row">
                    <div class="col-md-9">
                        <h4 style="margin-top:0">{{ $d->title }}</h4>
                        <p class="text-muted">
                            {{ $d->company->name }} &middot; {{ App\Models\placement\PlacementDrive::MODES[$d->drive_mode] }}
                            @if($d->drive_date) &middot; {{ $d->drive_date->format('d-m-Y') }}@endif
                            @if($d->venue) &middot; {{ $d->venue }}@endif
                        </p>
                        @if($d->description)<p>{!! nl2br(e($d->description)) !!}</p>@endif
                        @if($d->bond_details)<p><strong>Bond:</strong> {{ $d->bond_details }}</p>@endif
                        @if($item['jd_url'])<p><a href="{{ $item['jd_url'] }}" target="_blank" rel="noopener noreferrer"><i class="fa fa-download"></i> Job description</a></p>@endif
                    </div>
                    <div class="col-md-3 text-right">
                        <span class="label label-{{ $item['open'] ? 'success' : 'default' }}">{{ App\Models\placement\PlacementDrive::STATUSES[$d->status] }}</span>
                        @if($d->registration_deadline)<p><small>Register by {{ $d->registration_deadline->format('d-m-Y H:i') }}</small></p>@endif
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-condensed">
                        <thead><tr><th>Role</th><th>CTC (LPA)</th><th>Location</th><th></th></tr></thead>
                        <tbody>
                        @foreach($item['roles'] as $row)
                            @php($r = $row['role'])
                            @php($a = $row['application'])
                            <tr>
                                <td>{{ $r->title }}</td>
                                <td>{{ $r->ctc_min !== null ? rtrim(rtrim($r->ctc_min, '0'), '.') : '-' }}{{ $r->ctc_max !== null ? ' - '.rtrim(rtrim($r->ctc_max, '0'), '.') : '' }}</td>
                                <td>{{ $r->location ?: '-' }}</td>
                                <td class="text-right">
                                    @if($a && in_array($a->status, App\Models\placement\PlacementApplication::ACTIVE))
                                        <span class="label label-info">{{ App\Models\placement\PlacementApplication::STATUSES[$a->status] }}</span>
                                    @elseif($item['open'])
                                        <form method="post" action="{{ route('placement.student.apply', $d->id) }}" style="display:inline">
                                            @csrf <input type="hidden" name="role_id" value="{{ $r->id }}">
                                            <button class="btn btn-success btn-xs">Apply</button>
                                        </form>
                                    @else
                                        <span class="text-muted">Registration closed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="card text-center text-muted">No drives match your profile right now.</div>
        @endforelse
    </div>

    @include('includes.footerJs')
    @include('includes.footer')
</div>
