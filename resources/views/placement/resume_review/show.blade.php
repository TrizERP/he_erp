@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php $rv = $data['review']; $p = $data['profile']; $s = $data['student']; $canAct = App\Services\Placement\PlacementAccess::allows(['tpo', 'coordinator']); @endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8"><h4 class="page-title">Resume review &mdash; {{ $s->name }} <small>{{ $s->enrollment_no }} &middot; {{ $s->standard_name }}</small></h4></div>
            <div class="col-lg-4 text-right"><a href="{{ route('placement.resume_reviews.index') }}" class="btn btn-default">Back</a></div>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger"><ul style="margin:0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <div class="row">
            <div class="col-md-7"><div class="card">
                <table class="table table-condensed">
                    <tr><th style="width:30%">Requested</th><td>{{ $rv->requested_at->format('d-m-Y H:i') }}</td></tr>
                    <tr><th>Student's note</th><td>{{ $rv->request_note ?: '-' }}</td></tr>
                    <tr><th>Resume</th><td>@if($data['resume_url'])<a href="{{ $data['resume_url'] }}" target="_blank" rel="noopener noreferrer">Open resume</a>@else Not uploaded @endif</td></tr>
                    <tr><th>CGPA / backlogs</th><td>{{ $p->cgpa ?? '-' }} / {{ $p->active_backlogs ?? 0 }}</td></tr>
                    <tr><th>Skills</th><td>{{ $p ? $p->skills->pluck('skill_name')->implode(', ') : '-' }}</td></tr>
                </table>
            </div></div>
            <div class="col-md-5"><div class="card">
                <h5>Feedback</h5>
                @if($rv->status === 'reviewed')
                    <p>{!! nl2br(e($rv->feedback)) !!}</p><small class="text-muted">Reviewed {{ $rv->reviewed_at?->format('d-m-Y H:i') }}</small>
                @elseif($canAct)
                    <form method="post" action="{{ route('placement.resume_reviews.respond', $rv->id) }}">
                        @csrf
                        <div class="form-group"><textarea name="feedback" class="form-control" rows="6" required minlength="10" placeholder="Structure, content, formatting, what to add or cut..."></textarea></div>
                        <button class="btn btn-success btn-sm">Send feedback</button>
                    </form>
                @endif
            </div></div>
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
