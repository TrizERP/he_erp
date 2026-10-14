@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php
    $c = $data['company'];
    $canWrite = App\Services\Placement\PlacementAccess::allows(['tpo']);
    $badge = ['prospect' => 'default', 'invited' => 'info', 'confirmed' => 'primary', 'visited' => 'success', 'blacklisted' => 'danger'][$c->status] ?? 'default';
@endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">{{ $c->name }} <span class="label label-{{ $badge }}">{{ $data['statuses'][$c->status] ?? $c->status }}</span></h4>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                <a href="{{ route('placement.companies.index') }}" class="btn btn-default">Back</a>
                @if($canWrite)
                    <a href="{{ route('placement.companies.edit', $c->id) }}" class="btn btn-info">Edit</a>
                    <form method="post" action="{{ route('placement.companies.portal_link', $c->id) }}" style="display:inline">@csrf<button class="btn btn-default" title="Private link where the recruiter can post a requirement (14 days)">Recruiter link</button></form>
                    <form method="post" action="{{ route('placement.companies.destroy', $c->id) }}" style="display:inline"
                          onsubmit="return confirm('Delete this company?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger">Delete</button>
                    </form>
                @endif
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
                    <div class="row">
                        @if($data['logo_url'])
                            <div class="col-md-2"><img src="{{ $data['logo_url'] }}" alt="logo" class="img-responsive"></div>
                        @endif
                        <div class="{{ $data['logo_url'] ? 'col-md-10' : 'col-md-12' }}">
                            <table class="table table-condensed">
                                <tr><th style="width:30%">Industry</th><td>{{ $data['industry'] ?: '-' }}</td></tr>
                                <tr><th>Website</th><td>@if($c->website)<a href="{{ $c->website }}" target="_blank" rel="noopener noreferrer">{{ $c->website }}</a>@else - @endif</td></tr>
                                <tr><th>Head office</th><td>{{ $c->hq_location ?: '-' }}</td></tr>
                                <tr><th>Address</th><td>{{ $c->address ?: '-' }}</td></tr>
                                <tr><th>MoU</th><td>{{ $c::MOU_STATUSES[$c->mou_status] ?? $c->mou_status }}@if($c->mou_valid_till) (till {{ \Carbon\Carbon::parse($c->mou_valid_till)->format('d-m-Y') }})@endif</td></tr>
                                <tr><th>GST / PAN</th><td>{{ $data['gst_no'] ?: '-' }} / {{ $data['pan_no'] ?: '-' }}</td></tr>
                                <tr><th>Past visits</th><td>{!! nl2br(e($c->visit_history ?: '-')) !!}</td></tr>
                                @if($c->status === 'blacklisted')
                                    <tr class="danger"><th>Blacklist reason</th><td>{{ $c->blacklist_reason }}</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Contacts --}}
                <div class="card">
                    <h5>Contacts</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Name</th><th>Type</th><th>Designation</th><th>Email</th><th>Mobile</th><th></th></tr></thead>
                            <tbody>
                            @forelse($c->contacts as $ct)
                                <tr>
                                    <td>{{ $ct->name }} @if($ct->is_primary)<span class="label label-success">Primary</span>@endif</td>
                                    <td>{{ strtoupper($ct->contact_type) }}</td>
                                    <td>{{ $ct->designation }}</td>
                                    <td>{{ $ct->email }}</td>
                                    <td>{{ $ct->mobile }}</td>
                                    <td>
                                        @if($canWrite)
                                            <form method="post" action="{{ route('placement.companies.contacts.destroy', [$c->id, $ct->id]) }}" onsubmit="return confirm('Remove contact?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">No contacts yet.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($canWrite)
                        <form method="post" action="{{ route('placement.companies.contacts.store', $c->id) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-3 form-group"><input name="name" class="form-control" placeholder="Name *" required></div>
                                <div class="col-md-2 form-group">
                                    <select name="contact_type" class="form-control"><option value="hr">HR</option><option value="poc">POC</option><option value="other">Other</option></select>
                                </div>
                                <div class="col-md-2 form-group"><input name="designation" class="form-control" placeholder="Designation"></div>
                                <div class="col-md-2 form-group"><input name="email" type="email" class="form-control" placeholder="Email"></div>
                                <div class="col-md-2 form-group"><input name="mobile" class="form-control" placeholder="Mobile"></div>
                                <div class="col-md-1 form-group"><label><input type="checkbox" name="is_primary" value="1"> Primary</label></div>
                                <div class="col-md-12"><button class="btn btn-success btn-sm">Add contact</button></div>
                            </div>
                        </form>
                    @endif
                </div>

                {{-- Communication log --}}
                <div class="card">
                    <h5>Communication log &amp; follow-ups</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>When</th><th>Mode</th><th>Subject / notes</th><th>Follow-up</th><th></th></tr></thead>
                            <tbody>
                            @forelse($c->communications as $cm)
                                <tr>
                                    <td>{{ $cm->communicated_at->format('d-m-Y H:i') }}</td>
                                    <td>{{ ucfirst($cm->mode) }}</td>
                                    <td><strong>{{ $cm->subject }}</strong><br>{!! nl2br(e($cm->notes)) !!}</td>
                                    <td>
                                        @if($cm->follow_up_date)
                                            {{ $cm->follow_up_date->format('d-m-Y') }}
                                            @if($cm->follow_up_done)<span class="label label-success">Done</span>
                                            @elseif($cm->follow_up_date->isPast())<span class="label label-danger">Overdue</span>@endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($canWrite && $cm->follow_up_date && ! $cm->follow_up_done)
                                            <form method="post" action="{{ route('placement.companies.followups.done', [$c->id, $cm->id]) }}">
                                                @csrf
                                                <button class="btn btn-xs btn-success">Mark done</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Nothing logged yet.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($canWrite)
                        <form method="post" action="{{ route('placement.companies.communications.store', $c->id) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-2 form-group">
                                    <select name="mode" class="form-control"><option value="email">Email</option><option value="call">Call</option><option value="meeting">Meeting</option><option value="visit">Visit</option><option value="other">Other</option></select>
                                </div>
                                <div class="col-md-2 form-group">
                                    <select name="contact_id" class="form-control">
                                        <option value="">Contact</option>
                                        @foreach($c->contacts as $ct)<option value="{{ $ct->id }}">{{ $ct->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 form-group"><input type="datetime-local" name="communicated_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required></div>
                                <div class="col-md-3 form-group"><input type="date" name="follow_up_date" class="form-control" title="Follow-up date"></div>
                                <div class="col-md-12 form-group"><input name="subject" class="form-control" placeholder="Subject"></div>
                                <div class="col-md-12 form-group"><textarea name="notes" class="form-control" rows="2" placeholder="Notes *" required></textarea></div>
                                <div class="col-md-12"><button class="btn btn-success btn-sm">Log communication</button></div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="col-md-4">
                {{-- Status workflow --}}
                <div class="card">
                    <h5>Status workflow</h5>
                    <p class="text-muted">Prospect &rarr; Invited &rarr; Confirmed &rarr; Visited (Blacklisted at any stage)</p>
                    @if($canWrite && count($data['next_statuses']))
                        <form method="post" action="{{ route('placement.companies.status', $c->id) }}">
                            @csrf
                            <div class="form-group">
                                <label>Move to</label>
                                <select name="status" id="status-select" class="form-control" required>
                                    @foreach($data['next_statuses'] as $s)<option value="{{ $s }}">{{ $data['statuses'][$s] }}</option>@endforeach
                                </select>
                            </div>
                            <div class="form-group" id="reason-box">
                                <label>Reason <small class="text-muted">(required for blacklist changes)</small></label>
                                <textarea name="reason" class="form-control" rows="2"></textarea>
                            </div>
                            <button class="btn btn-primary btn-sm">Update status</button>
                        </form>
                    @endif
                </div>

                {{-- Feedback & rating --}}
                <div class="card">
                    <h5>Feedback &amp; rating</h5>
                    <p>
                        <strong>{{ $c->rating ? number_format($c->rating, 1).' / 5' : 'Not rated' }}</strong><br>
                        {!! nl2br(e($c->feedback)) !!}
                    </p>
                    @if($canWrite)
                        <form method="post" action="{{ route('placement.companies.feedback', $c->id) }}">
                            @csrf
                            <div class="form-group"><input type="number" name="rating" min="1" max="5" step="0.5" class="form-control" placeholder="Rating 1-5" value="{{ $c->rating }}" required></div>
                            <div class="form-group"><textarea name="feedback" class="form-control" rows="3" placeholder="Feedback after the visit">{{ $c->feedback }}</textarea></div>
                            <button class="btn btn-success btn-sm">Save</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    @include('includes.footer')
</div>
