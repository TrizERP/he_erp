@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php
    $t = $data['training'];
    $canWrite = App\Services\Placement\PlacementAccess::allows(['tpo']);
    $canAct = App\Services\Placement\PlacementAccess::allows(['tpo', 'coordinator']);
    $isInterview = $t->training_type === 'mock_interview';
@endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">{{ $t->title }} <span class="label label-info">{{ $data['types'][$t->training_type] ?? '' }}</span> <span class="label label-default">{{ ucfirst($t->status) }}</span></h4>
                <small>{{ $t->session_date?->format('d-m-Y') }} {{ $t->start_time ? substr($t->start_time, 0, 5) : '' }} &middot; {{ $t->venue }} &middot; Trainer: {{ $t->trainer ?: '-' }} &middot; {{ $data['standards'] }}</small>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                <a href="{{ route('placement.training.index') }}" class="btn btn-default">Back</a>
                @if($canWrite)
                    <a href="{{ route('placement.training.edit', $t->id) }}" class="btn btn-info">Edit</a>
                    <form method="post" action="{{ route('placement.training.assign', $t->id) }}" style="display:inline">@csrf<button class="btn btn-default">Add new students</button></form>
                    <form method="post" action="{{ route('placement.training.destroy', $t->id) }}" style="display:inline" onsubmit="return confirm('Delete this session?')">@csrf @method('DELETE')<button class="btn btn-danger">Delete</button></form>
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

        <form method="post" action="{{ route('placement.training.save', $t->id) }}">
            @csrf
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Enrollment</th><th>Student</th><th>Course</th><th>Attendance</th>
                            <th>{{ $isInterview ? 'Rating (1-5)' : 'Score'.($t->max_score ? ' / '.$t->max_score : '') }}</th><th>Feedback</th></tr></thead>
                        <tbody>
                        @forelse($data['rows'] as $r)
                            <tr>
                                <td>{{ $r->enrollment_no }}</td><td>{{ $r->student }}</td><td>{{ $r->course }}</td>
                                <td><select name="rows[{{ $r->student_id }}][attendance]" class="form-control input-sm" @disabled(! $canAct)>
                                    @foreach(['pending' => 'Pending', 'present' => 'Present', 'absent' => 'Absent'] as $k => $l)<option value="{{ $k }}" @selected($r->attendance == $k)>{{ $l }}</option>@endforeach</select></td>
                                <td style="width:110px">
                                    @if($isInterview)
                                        <input type="number" min="1" max="5" name="rows[{{ $r->student_id }}][rating]" class="form-control input-sm" value="{{ $r->rating }}" @disabled(! $canAct)>
                                    @else
                                        <input type="number" step="0.01" min="0" name="rows[{{ $r->student_id }}][score]" class="form-control input-sm" value="{{ $r->score }}" @disabled(! $canAct)>
                                    @endif
                                </td>
                                <td><input name="rows[{{ $r->student_id }}][feedback]" class="form-control input-sm" value="{{ $r->feedback }}" @disabled(! $canAct)></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No students assigned. Only registered students of the selected courses are added.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($canAct && $data['rows']->count())<button class="btn btn-primary btn-sm">Save</button>@endif
            </div>
        </form>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
