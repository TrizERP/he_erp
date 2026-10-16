@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Fees Waiver</h4>
            </div>
        </div>
        <div class="card">
            @if(!empty($data['message']))
            <div class="alert {{ $data['status_code'] == 1 ? 'alert-success' : 'alert-danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">x</button>
                <strong>{{ $data['message'] }}</strong>
            </div>
            @endif

            <form action="{{ route('fees_waiver.index') }}" method="get" class="row">
                <div class="col-md-4 form-group">
                    <label>Search Student (name / enrollment no / mobile)</label>
                    <input type="text" name="search" value="{{ $data['search'] }}" class="form-control" required>
                </div>
                <div class="col-md-2 form-group" style="margin-top:25px;">
                    <button type="submit" class="btn btn-success">Search</button>
                </div>
            </form>

            @if($data['search'] !== '')
            <form action="{{ route('fees_waiver.store') }}" method="post">
            @csrf
            <input type="hidden" name="search" value="{{ $data['search'] }}">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Enrollment No</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Fees Waiver</th>
                            <th>Remarks</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data['students'] as $stu)
                        <tr>
                                <td>{{ $stu->enrollment_no }}
                                </td>
                                <td>{{ trim($stu->first_name.' '.$stu->middle_name.' '.$stu->last_name) }}</td>
                                <td>{{ $stu->standard_name }} {{ $stu->division_name }}</td>
                                <td><input type="number" name="amount[{{ $stu->id }}]" min="0" step="0.01" value="{{ $stu->amount ?? 0 }}" class="form-control" required></td>
                                <td><input type="text" name="remarks[{{ $stu->id }}]" maxlength="255" value="{{ $stu->remarks }}" class="form-control"></td>
                                <td>
                                    <button type="submit" name="student_id" value="{{ $stu->id }}" class="btn btn-primary">Save</button>
                                    @if($stu->applied_at)
                                        <span class="label label-info">Applied {{ \Carbon\Carbon::parse($stu->applied_at)->format('d-m-Y') }}</span>
                                    @endif
                                </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No student found</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            </form>
            @endif
        </div>
    </div>
</div>

@include('includes.footerJs')
@include('includes.footer')
