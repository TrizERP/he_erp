@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php
    $p = $data['profile'];
    $s = $data['student'];
    $skills = old('skills', $p->skills->map(fn ($k) => ['name' => $k->skill_name, 'level' => $k->level])->all());
    while (count($skills) < 5) { $skills[] = ['name' => '', 'level' => 'intermediate']; }
    $projects = old('projects', $p->projects ?? []);
    while (count($projects) < 2) { $projects[] = ['title' => '', 'description' => '']; }
    $certs = old('certifications', $p->certifications ?? []);
    while (count($certs) < 2) { $certs[] = ['title' => '', 'issuer' => '']; }
    $prefs = $p->preferences ?? [];
    $vBadge = ['pending' => 'warning', 'verified' => 'success', 'rejected' => 'danger'][$p->verification_status];
@endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">My Placement Profile</h4>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                <a href="{{ route('placement.student.resume') }}" class="btn btn-info"><i class="fa fa-file-pdf-o"></i> Download resume (PDF)</a>
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
            <div class="col-md-4">
                <div class="card">
                    <h5>{{ $s->name ?? '' }}</h5>
                    <p class="text-muted">{{ $s->enrollment_no ?? '' }} &middot; {{ $s->standard_name ?? '' }}</p>
                    <p>Profile completeness</p>
                    <div class="progress"><div class="progress-bar progress-bar-success" style="width: {{ $p->completeness }}%">{{ $p->completeness }}%</div></div>
                    <p>Verification: <span class="label label-{{ $vBadge }}">{{ ucfirst($p->verification_status) }}</span></p>
                    @if($p->verification_remarks)<p class="text-muted">{{ $p->verification_remarks }}</p>@endif
                    @if($p->isDebarred())<p class="text-danger">Debarred until {{ $p->debarred_until->format('d-m-Y') }}</p>@endif
                </div>

                <div class="card">
                    <h5>Placement registration</h5>
                    @if($p->registration_status === 'registered')
                        <p><span class="label label-success">Registered</span> on {{ $p->undertaking_accepted_at?->format('d-m-Y') }}</p>
                        <form method="post" action="{{ route('placement.student.optout') }}" onsubmit="return confirm('Opt out of campus placements?')">
                            @csrf <button class="btn btn-danger btn-sm">Opt out</button>
                        </form>
                    @else
                        <p><span class="label label-default">{{ $p->registration_status === 'opted_out' ? 'Opted out' : 'Not registered' }}</span></p>
                        <form method="post" action="{{ route('placement.student.register') }}">
                            @csrf
                            <div class="checkbox"><label><input type="checkbox" name="undertaking" value="1">
                                I undertake that the details I provide are true, that I will attend drives I apply for, and that I will follow the institute's placement policy, including any one-offer rule.</label></div>
                            <button class="btn btn-success btn-sm">Register for placements</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <form method="post" action="{{ route('placement.student.profile.update') }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <h5>Academic details <small class="text-muted">(changing these re-sends your profile for verification)</small></h5>
                        <div class="row">
                            <div class="col-md-3 form-group"><label>CGPA</label><input type="number" step="0.01" min="0" max="10" name="cgpa" class="form-control" value="{{ old('cgpa', $p->cgpa) }}"></div>
                            <div class="col-md-3 form-group"><label>10th %</label><input type="number" step="0.01" min="0" max="100" name="tenth_percent" class="form-control" value="{{ old('tenth_percent', $p->tenth_percent) }}"></div>
                            <div class="col-md-3 form-group"><label>12th %</label><input type="number" step="0.01" min="0" max="100" name="twelfth_percent" class="form-control" value="{{ old('twelfth_percent', $p->twelfth_percent) }}"></div>
                            <div class="col-md-3 form-group"><label>Active backlogs</label><input type="number" min="0" name="active_backlogs" class="form-control" required value="{{ old('active_backlogs', $p->active_backlogs) }}"></div>
                            <div class="col-md-3 form-group"><label>Passing year</label><input type="number" name="passing_year" class="form-control" value="{{ old('passing_year', $p->passing_year) }}"></div>
                        </div>

                        <h5>Skills</h5>
                        <datalist id="skill-list">@foreach($data['skill_options'] as $n)<option value="{{ $n }}">@endforeach</datalist>
                        @foreach($skills as $i => $k)
                            <div class="row">
                                <div class="col-md-6 form-group"><input name="skills[{{ $i }}][name]" list="skill-list" class="form-control" placeholder="Skill" value="{{ $k['name'] ?? '' }}"></div>
                                <div class="col-md-3 form-group">
                                    <select name="skills[{{ $i }}][level]" class="form-control">
                                        @foreach(App\Models\placement\PlacementStudentSkill::LEVELS as $v => $l)<option value="{{ $v }}" @selected(($k['level'] ?? 'intermediate') == $v)>{{ $l }}</option>@endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach

                        <h5>Projects</h5>
                        @foreach($projects as $i => $x)
                            <div class="row">
                                <div class="col-md-4 form-group"><input name="projects[{{ $i }}][title]" class="form-control" placeholder="Title" value="{{ $x['title'] ?? '' }}"></div>
                                <div class="col-md-8 form-group"><input name="projects[{{ $i }}][description]" class="form-control" placeholder="What you built" value="{{ $x['description'] ?? '' }}"></div>
                            </div>
                        @endforeach

                        <h5>Certifications</h5>
                        @foreach($certs as $i => $x)
                            <div class="row">
                                <div class="col-md-6 form-group"><input name="certifications[{{ $i }}][title]" class="form-control" placeholder="Certification" value="{{ $x['title'] ?? '' }}"></div>
                                <div class="col-md-6 form-group"><input name="certifications[{{ $i }}][issuer]" class="form-control" placeholder="Issued by" value="{{ $x['issuer'] ?? '' }}"></div>
                            </div>
                        @endforeach

                        <h5>Achievements <small class="text-muted">(one per line)</small></h5>
                        <div class="form-group"><textarea name="achievements" class="form-control" rows="3">{{ old('achievements', implode("\n", $p->achievements ?? [])) }}</textarea></div>

                        <h5>Links &amp; preferences</h5>
                        <div class="row">
                            <div class="col-md-4 form-group"><label>LinkedIn</label><input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $p->linkedin_url) }}"></div>
                            <div class="col-md-4 form-group"><label>GitHub</label><input type="url" name="github_url" class="form-control" value="{{ old('github_url', $p->github_url) }}"></div>
                            <div class="col-md-4 form-group"><label>Portfolio</label><input type="url" name="portfolio_url" class="form-control" value="{{ old('portfolio_url', $p->portfolio_url) }}"></div>
                            <div class="col-md-6 form-group"><label>Preferred locations (comma separated)</label><input name="preferred_locations" class="form-control" value="{{ old('preferred_locations', implode(', ', $prefs['locations'] ?? [])) }}"></div>
                            <div class="col-md-6 form-group"><label>Preferred job types</label>
                                @foreach($data['job_types'] as $jt)
                                    <label class="checkbox-inline"><input type="checkbox" name="preferred_job_types[]" value="{{ $jt }}" @checked(in_array($jt, $prefs['job_types'] ?? []))> {{ $jt }}</label>
                                @endforeach
                            </div>
                        </div>

                        <h5>Resume</h5>
                        <div class="form-group">
                            <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                            @if($data['resume_url'])<small><a href="{{ $data['resume_url'] }}" target="_blank" rel="noopener noreferrer">Current resume</a></small>@endif
                        </div>
                        <div class="text-center"><button class="btn btn-success">Save profile</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('includes.footerJs')
    @include('includes.footer')
</div>
