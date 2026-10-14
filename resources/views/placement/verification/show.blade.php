@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php
    $p = $data['profile'];
    $s = $data['student'];
    $canAct = App\Services\Placement\PlacementAccess::allows(['tpo', 'coordinator']);
@endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">{{ $s->name }} <small>{{ $s->enrollment_no }} &middot; {{ $s->standard_name }}</small></h4>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                <a href="{{ route('placement.verification.index') }}" class="btn btn-default">Back</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger"><ul style="margin:0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <table class="table table-condensed">
                        <tr><th style="width:30%">CGPA</th><td>{{ $p->cgpa ?? '-' }}</td></tr>
                        <tr><th>10th %</th><td>{{ $p->tenth_percent ?? '-' }}</td></tr>
                        <tr><th>12th %</th><td>{{ $p->twelfth_percent ?? '-' }}</td></tr>
                        <tr><th>Active backlogs</th><td>{{ $p->active_backlogs }}</td></tr>
                        <tr><th>Passing year</th><td>{{ $p->passing_year ?? '-' }}</td></tr>
                        <tr><th>Skills</th><td>{{ $p->skills->map(fn ($k) => $k->skill_name.' ('.$k->level.')')->implode(', ') ?: '-' }}</td></tr>
                        <tr><th>Projects</th><td>@forelse($p->projects ?? [] as $x)<div><strong>{{ $x['title'] }}</strong> {{ $x['description'] ?? '' }}</div>@empty - @endforelse</td></tr>
                        <tr><th>Certifications</th><td>@forelse($p->certifications ?? [] as $x)<div>{{ $x['title'] }} {{ ! empty($x['issuer']) ? '- '.$x['issuer'] : '' }}</div>@empty - @endforelse</td></tr>
                        <tr><th>Links</th><td>
                            @foreach(['linkedin_url' => 'LinkedIn', 'github_url' => 'GitHub', 'portfolio_url' => 'Portfolio'] as $f => $l)
                                @if($p->{$f})<a href="{{ $p->{$f} }}" target="_blank" rel="noopener noreferrer">{{ $l }}</a>&nbsp;@endif
                            @endforeach
                        </td></tr>
                        <tr><th>Resume</th><td>@if($data['resume_url'])<a href="{{ $data['resume_url'] }}" target="_blank" rel="noopener noreferrer">Download</a>@else Not uploaded @endif</td></tr>
                        <tr><th>Completeness</th><td>{{ $p->completeness }}%</td></tr>
                    </table>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h5>Decision</h5>
                    <p>Current: <span class="label label-{{ ['pending' => 'warning', 'verified' => 'success', 'rejected' => 'danger'][$p->verification_status] }}">{{ ucfirst($p->verification_status) }}</span></p>
                    @if($p->verification_remarks)<p class="text-muted">{{ $p->verification_remarks }}</p>@endif
                    @if($canAct)
                        <form method="post" action="{{ route('placement.verification.decide', $p->id) }}">
                            @csrf
                            <div class="form-group"><textarea name="remarks" class="form-control" rows="3" placeholder="Remarks (required to reject)"></textarea></div>
                            <button name="status" value="verified" class="btn btn-success btn-sm">Verify</button>
                            <button name="status" value="rejected" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    @include('includes.footer')
</div>
