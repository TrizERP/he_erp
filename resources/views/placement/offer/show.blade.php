@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php
    $o = $data['offer'];
    $s = $data['student'];
    $canWrite = App\Services\Placement\PlacementAccess::allows(['tpo']);
    $badge = ['offered' => 'info', 'accepted' => 'primary', 'rejected' => 'default', 'revoked' => 'danger', 'joined' => 'success', 'not_joined' => 'warning'][$o->status] ?? 'default';
@endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">Offer #{{ $o->id }} <span class="label label-{{ $badge }}">{{ $data['statuses'][$o->status] }}</span>
                    @if($o->is_ppo)<span class="label label-info">PPO</span>@endif</h4>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                <a href="{{ route('placement.offers.index') }}" class="btn btn-default">Back</a>
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

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <table class="table table-condensed">
                        <tr><th style="width:30%">Student</th><td>{{ $s->name ?? '' }} ({{ $s->enrollment_no ?? '' }}) {{ $s->standard_name ?? '' }}</td></tr>
                        <tr><th>Company</th><td>{{ $o->company->name ?? '' }}</td></tr>
                        <tr><th>Drive</th><td>{{ $o->drive->title ?? ($o->is_ppo ? 'PPO conversion' : '-') }}</td></tr>
                        <tr><th>Role</th><td>{{ $o->role_title }}</td></tr>
                        <tr><th>CTC</th><td>Fixed {{ $o->ctc_fixed }} + variable {{ $o->ctc_variable }} = <strong>{{ $o->ctc_total }} LPA</strong>; joining bonus {{ $o->joining_bonus }} LPA</td></tr>
                        <tr><th>Location</th><td>{{ $o->location ?: '-' }}</td></tr>
                        <tr><th>Offer / joining date</th><td>{{ $o->offer_date?->format('d-m-Y') }} / {{ $o->joining_date?->format('d-m-Y') ?: '-' }}</td></tr>
                        <tr><th>Offer letter</th><td>@if($data['letter_url'])<a href="{{ $data['letter_url'] }}" target="_blank" rel="noopener noreferrer">Download</a>@else - @endif</td></tr>
                        <tr><th>Bond</th><td>{!! nl2br(e($o->bond_details ?: '-')) !!}</td></tr>
                        <tr><th>Student response</th><td>{{ $o->responded_at?->format('d-m-Y H:i') ?: 'Awaiting' }} {{ $o->status_reason ? '- '.$o->status_reason : '' }}</td></tr>
                        <tr><th>TPO verification</th><td>{{ $o->verified_at ? 'Verified '.$o->verified_at->format('d-m-Y H:i') : 'Not verified' }}</td></tr>
                        @if($o->override_reason)<tr class="warning"><th>Policy override</th><td>{{ $o->override_reason }}</td></tr>@endif
                    </table>
                </div>
            </div>
            <div class="col-md-4">
                @if($canWrite && count($data['next']))
                    <div class="card">
                        <h5>Change status</h5>
                        <form method="post" action="{{ route('placement.offers.status', $o->id) }}">
                            @csrf
                            <div class="form-group">
                                <select name="status" class="form-control">@foreach($data['next'] as $n)<option value="{{ $n }}">{{ $data['statuses'][$n] }}</option>@endforeach</select>
                            </div>
                            <div class="form-group"><textarea name="reason" class="form-control" rows="2" placeholder="Reason (required to reject, revoke or mark not joined)"></textarea></div>
                            <div class="form-group"><input name="override_reason" class="form-control" placeholder="Policy override reason (only if blocked)"></div>
                            <button class="btn btn-primary btn-sm">Update</button>
                        </form>
                    </div>
                @endif
                @if($canWrite && $o->status === 'accepted' && ! $o->verified_at)
                    <div class="card">
                        <h5>Verify acceptance</h5>
                        <p class="text-muted">Confirm you have seen the signed acceptance / offer letter.</p>
                        <form method="post" action="{{ route('placement.offers.verify', $o->id) }}">@csrf<button class="btn btn-success btn-sm">Verify</button></form>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
