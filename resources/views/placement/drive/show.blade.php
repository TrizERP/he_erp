@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php
    $d = $data['drive'];
    $canWrite = App\Services\Placement\PlacementAccess::allows(['tpo']);
    $canAct = App\Services\Placement\PlacementAccess::allows(['tpo', 'coordinator']);
    $editable = $data['editable'];
    $roleNames = $d->roles->pluck('title', 'id');
    $badge = ['draft' => 'default', 'published' => 'info', 'registration_open' => 'success', 'registration_closed' => 'warning', 'in_progress' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'][$d->status] ?? 'default';
@endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">{{ $d->title }} <span class="label label-{{ $badge }}">{{ $data['statuses'][$d->status] }}</span></h4>
                <small>{{ $d->company->name ?? '' }}</small>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                <a href="{{ route('placement.drives.index') }}" class="btn btn-default">Back</a>
                @if(! in_array($d->status, ['draft']))
                    <a href="{{ route('placement.rounds.index', $d->id) }}" class="btn btn-primary">Rounds</a>
                    @if($canWrite)
                        <form method="post" action="{{ route('placement.drives.portal_link', $d->id) }}" style="display:inline">@csrf<button class="btn btn-default" title="Creates a private link for the recruiter (14 days)">Recruiter link</button></form>
                    @endif
                @endif
                @if($canWrite && ! in_array($d->status, ['completed', 'cancelled']))
                    <a href="{{ route('placement.drives.edit', $d->id) }}" class="btn btn-info">Edit</a>
                @endif
                @if($canWrite && $d->status === 'draft')
                    <form method="post" action="{{ route('placement.drives.destroy', $d->id) }}" style="display:inline" onsubmit="return confirm('Delete this draft drive?')">
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
            <div class="col-md-4 col-sm-4"><div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $data['counts']['eligible'] }}</h3><small>Eligible students</small></div></div>
            <div class="col-md-4 col-sm-4"><div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $data['counts']['applications'] }}</h3><small>Applications</small></div></div>
            <div class="col-md-4 col-sm-4"><div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $data['counts']['shortlisted'] }}</h3><small>Shortlisted</small></div></div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <table class="table table-condensed">
                        <tr><th style="width:25%">Mode</th><td>{{ $data['modes'][$d->drive_mode] ?? $d->drive_mode }}</td></tr>
                        <tr><th>Date / time</th><td>{{ $d->drive_date?->format('d-m-Y') ?: '-' }} {{ $d->start_time ? substr($d->start_time, 0, 5) : '' }}{{ $d->end_time ? ' - '.substr($d->end_time, 0, 5) : '' }}</td></tr>
                        <tr><th>Venue</th><td>{{ $d->venue ?: '-' }}</td></tr>
                        <tr><th>Registration deadline</th><td>{{ $d->registration_deadline?->format('d-m-Y H:i') ?: '-' }}</td></tr>
                        <tr><th>JD</th><td>@if($data['jd_url'])<a href="{{ $data['jd_url'] }}" target="_blank" rel="noopener noreferrer">Download</a>@else - @endif</td></tr>
                        <tr><th>Bond</th><td>{!! nl2br(e($d->bond_details ?: '-')) !!}</td></tr>
                        <tr><th>Description</th><td>{!! nl2br(e($d->description ?: '-')) !!}</td></tr>
                        @if($d->status === 'cancelled')<tr class="danger"><th>Cancel reason</th><td>{{ $d->cancel_reason }}</td></tr>@endif
                    </table>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h5>Workflow</h5>
                    <p class="text-muted">Draft &rarr; Published &rarr; Registration Open &rarr; Registration Closed &rarr; In Progress &rarr; Completed</p>
                    @if($canWrite && count($data['next_statuses']))
                        <form method="post" action="{{ route('placement.drives.status', $d->id) }}">
                            @csrf
                            <div class="form-group">
                                <select name="status" class="form-control" id="drive-status">
                                    @foreach($data['next_statuses'] as $s)<option value="{{ $s }}">{{ $data['statuses'][$s] }}</option>@endforeach
                                </select>
                            </div>
                            <div class="form-group"><textarea name="reason" class="form-control" rows="2" placeholder="Reason (required to cancel)"></textarea></div>
                            <button class="btn btn-primary btn-sm">Move</button>
                        </form>
                        @if($d->status === 'draft')<p class="text-muted" style="margin-top:8px"><small>Publishing needs a role, a future registration deadline and computes the eligible list.</small></p>@endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Roles --}}
        <div class="card">
            <h5>Roles &amp; packages</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Role</th><th>CTC (LPA)</th><th>Stipend / month</th><th>Location</th><th>Openings</th><th></th></tr></thead>
                    <tbody>
                    @forelse($d->roles as $r)
                        <tr>
                            <td>{{ $r->title }}</td>
                            <td>{{ $r->ctc_min !== null ? rtrim(rtrim($r->ctc_min, '0'), '.') : '-' }}{{ $r->ctc_max !== null ? ' - '.rtrim(rtrim($r->ctc_max, '0'), '.') : '' }}</td>
                            <td>{{ $r->stipend ?? '-' }}</td>
                            <td>{{ $r->location ?: '-' }}</td>
                            <td>{{ $r->openings ?? '-' }}</td>
                            <td>
                                @if($canWrite && $editable)
                                    <form method="post" action="{{ route('placement.drives.roles.destroy', [$d->id, $r->id]) }}" onsubmit="return confirm('Remove role?')">
                                        @csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No roles yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($canWrite && $editable)
                <form method="post" action="{{ route('placement.drives.roles.store', $d->id) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group"><input name="title" class="form-control" placeholder="Role title *" required list="role-list"></div>
                        <datalist id="role-list">@foreach($data['job_roles'] as $n)<option value="{{ $n }}">@endforeach</datalist>
                        <div class="col-md-2 form-group">
                            <select name="job_type_id" class="form-control"><option value="">Job type</option>@foreach($data['job_types'] as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select>
                        </div>
                        <div class="col-md-1 form-group"><input name="ctc_min" type="number" step="0.01" min="0" class="form-control" placeholder="CTC min"></div>
                        <div class="col-md-1 form-group"><input name="ctc_max" type="number" step="0.01" min="0" class="form-control" placeholder="CTC max"></div>
                        <div class="col-md-1 form-group"><input name="stipend" type="number" step="0.01" min="0" class="form-control" placeholder="Stipend"></div>
                        <div class="col-md-2 form-group"><input name="location" class="form-control" placeholder="Location"></div>
                        <div class="col-md-1 form-group"><input name="openings" type="number" min="1" class="form-control" placeholder="Seats"></div>
                        <div class="col-md-1"><button class="btn btn-success btn-sm">Add</button></div>
                    </div>
                </form>
            @endif
        </div>

        {{-- Eligibility --}}
        <div class="card">
            <h5>Eligibility criteria <small class="text-muted">(a role-specific row overrides the "all roles" row)</small></h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Applies to</th><th>Min CGPA</th><th>10th %</th><th>12th %</th><th>Max backlogs</th><th>Passing year</th><th>Courses</th><th>Gender</th><th></th></tr></thead>
                    <tbody>
                    @forelse($d->eligibilities as $e)
                        <tr>
                            <td>{{ $e->role_id ? ($roleNames[$e->role_id] ?? 'Role #'.$e->role_id) : 'All roles' }}</td>
                            <td>{{ $e->min_cgpa ?? '-' }}</td><td>{{ $e->min_10th_percent ?? '-' }}</td><td>{{ $e->min_12th_percent ?? '-' }}</td>
                            <td>{{ $e->max_active_backlogs ?? '-' }}</td><td>{{ $e->passing_year ?? '-' }}</td>
                            <td>{{ $e->standard_ids ? collect($e->standard_ids)->map(fn ($id) => $data['standards'][$id] ?? $id)->implode(', ') : 'All' }}</td>
                            <td>{{ $e->gender ? ucfirst($e->gender) : '-' }}</td>
                            <td>
                                @if($canWrite && $editable)
                                    <form method="post" action="{{ route('placement.drives.eligibility.destroy', [$d->id, $e->id]) }}" onsubmit="return confirm('Remove criteria?')">
                                        @csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">No criteria: every registered, verified student is eligible.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($canWrite && $editable)
                <form method="post" action="{{ route('placement.drives.eligibility.save', $d->id) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <select name="role_id" class="form-control"><option value="">All roles</option>@foreach($roleNames as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select>
                        </div>
                        <div class="col-md-3 form-group">
                            <select name="template_id" class="form-control"><option value="">Start from template (optional)</option>@foreach($data['templates'] as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
                        </div>
                        <div class="col-md-1 form-group"><input name="min_cgpa" type="number" step="0.01" min="0" max="10" class="form-control" placeholder="CGPA"></div>
                        <div class="col-md-1 form-group"><input name="min_10th_percent" type="number" step="0.01" class="form-control" placeholder="10th"></div>
                        <div class="col-md-1 form-group"><input name="min_12th_percent" type="number" step="0.01" class="form-control" placeholder="12th"></div>
                        <div class="col-md-1 form-group"><input name="max_active_backlogs" type="number" min="0" class="form-control" placeholder="Backlog"></div>
                        <div class="col-md-2 form-group"><input name="passing_year" type="number" class="form-control" placeholder="Passing yr"></div>
                        <div class="col-md-6 form-group">
                            <select name="standard_ids[]" class="form-control" multiple size="4">@foreach($data['standards'] as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select>
                            <small class="text-muted">Allowed courses (Ctrl-click; none = all)</small>
                        </div>
                        <div class="col-md-2 form-group">
                            <select name="gender" class="form-control"><option value="">Any gender</option><option value="male">Male</option><option value="female">Female</option></select>
                        </div>
                        <div class="col-md-12"><button class="btn btn-success btn-sm">Save criteria</button></div>
                    </div>
                </form>
            @endif
            @if($canWrite && in_array($d->status, ['published', 'registration_open']))
                <form method="post" action="{{ route('placement.drives.recompute', $d->id) }}" style="margin-top:10px">
                    @csrf <button class="btn btn-warning btn-sm"><i class="fa fa-refresh"></i> Recompute eligible list</button>
                </form>
            @endif
        </div>

        {{-- Eligible list & applicants --}}
        <div class="card">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" href="#tab-eligible" data-toggle="tab">Eligibility list</a></li>
                <li class="nav-item"><a class="nav-link" href="#tab-applicants" data-toggle="tab">Applicants</a></li>
            </ul>
            <div class="tab-content" style="padding-top:15px">
                <div class="tab-pane active" id="tab-eligible">
                    <div class="row"><div class="col-md-3 form-group">
                        <select id="f-eligible" class="form-control"><option value="">All</option><option value="1">Eligible</option><option value="0">Not eligible</option></select>
                    </div></div>
                    <div class="table-responsive">
                        <table id="pl-eligible" class="table table-striped" style="width:100%">
                            <thead><tr><th>#</th><th>Enrollment</th><th>Student</th><th>Course</th><th>Role</th><th>Result</th><th>Reasons</th></tr></thead>
                        </table>
                    </div>
                </div>
                <div class="tab-pane" id="tab-applicants">
                    <form method="post" id="bulk-form" action="{{ route('placement.drives.applicants.status', $d->id) }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-2 form-group">
                                <select id="f-app-status" class="form-control"><option value="">All statuses</option>@foreach(App\Models\placement\PlacementApplication::STATUSES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
                            </div>
                            <div class="col-md-10 text-right">
                                <a class="btn btn-default btn-sm" href="{{ route('placement.drives.applicants.export', $d->id) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="pl-applicants" class="table table-striped" style="width:100%">
                                <thead><tr><th><input type="checkbox" id="app-all"></th><th>#</th><th>Enrollment</th><th>Student</th><th>Course</th><th>Role</th><th>CGPA</th><th>Backlogs</th><th>Status</th><th>Applied</th></tr></thead>
                            </table>
                        </div>
                        @if($canAct)
                            <div class="row" style="margin-top:10px">
                                <div class="col-md-2 form-group">
                                    <select name="status" class="form-control"><option value="shortlisted">Shortlist</option><option value="rejected">Reject</option><option value="applied">Reset to applied</option></select>
                                </div>
                                <div class="col-md-3 form-group"><input name="remarks" class="form-control" placeholder="Remarks"></div>
                                <div class="col-md-5 form-group"><input name="override_reason" class="form-control" placeholder="Override reason (only to force a policy block)"></div>
                                <div class="col-md-2"><button class="btn btn-primary btn-sm" id="bulk-submit">Apply to selected</button></div>
                            </div>
                        @endif
                    </form>
                    @if($canWrite && in_array($d->status, ['registration_open', 'registration_closed', 'in_progress']))
                        <hr>
                        <h6>Add a student despite a block (override)</h6>
                        <form method="post" action="{{ route('placement.drives.applicants.override', $d->id) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-3 form-group"><input name="enrollment_no" class="form-control" placeholder="Enrollment no *" required></div>
                                <div class="col-md-3 form-group">
                                    <select name="role_id" class="form-control" required><option value="">Role *</option>@foreach($roleNames as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select>
                                </div>
                                <div class="col-md-4 form-group"><input name="override_reason" class="form-control" placeholder="Reason (min 10 chars, audited) *" required minlength="10"></div>
                                <div class="col-md-2"><button class="btn btn-warning btn-sm">Add with override</button></div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            var eligible = $('#pl-eligible').DataTable({
                processing: true, serverSide: true,
                ajax: {url: "{{ route('placement.drives.eligible.data', $d->id) }}", data: function (x) { x.eligible = $('#f-eligible').val(); }},
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'enrollment_no', name: 's.enrollment_no'},
                    {data: 'student', name: 'student', orderable: false, searchable: false},
                    {data: 'course', name: 'st.name'},
                    {data: 'role', name: 'r.title'},
                    {data: 'is_eligible', name: 'pe.is_eligible'},
                    {data: 'reasons', name: 'pe.reasons', defaultContent: ''}
                ]
            });
            $('#f-eligible').on('change', function () { eligible.ajax.reload(); });

            var applicants = $('#pl-applicants').DataTable({
                processing: true, serverSide: true,
                ajax: {url: "{{ route('placement.drives.applicants.data', $d->id) }}", data: function (x) { x.status = $('#f-app-status').val(); }},
                columns: [
                    {data: 'checkbox', orderable: false, searchable: false},
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'enrollment_no', name: 's.enrollment_no'},
                    {data: 'student', name: 'student', orderable: false, searchable: false},
                    {data: 'course', name: 'st.name'},
                    {data: 'role', name: 'r.title'},
                    {data: 'cgpa', name: 'p.cgpa', defaultContent: ''},
                    {data: 'active_backlogs', name: 'p.active_backlogs', defaultContent: ''},
                    {data: 'status', name: 'a.status'},
                    {data: 'applied_at', name: 'a.applied_at'}
                ]
            });
            $('#f-app-status').on('change', function () { applicants.ajax.reload(); });
            $('#app-all').on('change', function () { $('.app-check').prop('checked', this.checked); });

            // selected ids are submitted with the bulk form
            $('#bulk-form').on('submit', function () {
                var form = $(this);
                form.find('input[name="application_ids[]"]').remove();
                var ids = $('.app-check:checked');
                if (!ids.length) { alert('Select at least one applicant.'); return false; }
                ids.each(function () { form.append('<input type="hidden" name="application_ids[]" value="' + this.value + '">'); });
            });
        });
    </script>
    @include('includes.footer')
</div>
