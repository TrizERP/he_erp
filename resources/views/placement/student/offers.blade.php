@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title"><div class="col-lg-12"><h4 class="page-title">My Offers</h4></div></div>

        @if ($sessionData = Session::get('data'))
            <div class="alert {{ $sessionData['status_code'] == 1 ? 'alert-success' : 'alert-danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $sessionData['message'] }}</strong>
            </div>
        @endif

        @forelse($data['offers'] as $o)
            @php($badge = ['offered' => 'info', 'accepted' => 'primary', 'rejected' => 'default', 'revoked' => 'danger', 'joined' => 'success', 'not_joined' => 'warning'][$o->status] ?? 'default')
            <div class="card">
                <div class="row">
                    <div class="col-md-8">
                        <h4 style="margin-top:0">{{ $o->company->name ?? '' }} &mdash; {{ $o->role_title }}
                            <span class="label label-{{ $badge }}">{{ $data['statuses'][$o->status] }}</span>@if($o->is_ppo) <span class="label label-info">PPO</span>@endif</h4>
                        <p>
                            <strong>{{ $o->ctc_total }} LPA</strong> (fixed {{ $o->ctc_fixed }} + variable {{ $o->ctc_variable }}); joining bonus {{ $o->joining_bonus }} LPA<br>
                            {{ $o->location ?: '' }} @if($o->joining_date) &middot; joining {{ $o->joining_date->format('d-m-Y') }}@endif
                        </p>
                        @if($o->bond_details)<p><strong>Bond:</strong> {{ $o->bond_details }}</p>@endif
                        @if($o->verified_at)<p class="text-success"><i class="fa fa-check"></i> Verified by the placement cell</p>@endif
                    </div>
                    <div class="col-md-4">
                        @if($o->status === 'offered')
                            <form method="post" action="{{ route('placement.student.offers.respond', $o->id) }}">
                                @csrf
                                <div class="form-group"><textarea name="reason" class="form-control" rows="2" placeholder="Reason (needed if you decline)"></textarea></div>
                                <button name="decision" value="accepted" class="btn btn-success btn-sm" onclick="return confirm('Accept this offer? Under the placement policy this may close other offers.')">Accept</button>
                                <button name="decision" value="rejected" class="btn btn-danger btn-sm" onclick="return confirm('Decline this offer?')">Decline</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card text-center text-muted">No offers yet.</div>
        @endforelse
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
