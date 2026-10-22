@extends('pbas.layout')

@section('crumb', 'View/Lock Faculty Data')
@section('heading', 'Lock/Unlock  Data :  '.$departmentName)

@section('alerts')
    @if(session('pbas_locked'))
        <div class="alert alert-info alert-block">{{ session('pbas_locked') }}</div>
    @endif
    @if(session('pbas_unlocked'))
        <div class="alert alert-danger alert-block">{{ session('pbas_unlocked') }}</div>
    @endif
@endsection

@section('content')
    <div class="card">
        <h5 class="m-b-0">VIEW/LOCK FACULTY DATA</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>Faculty Id</th>
                    <th>Faculty Name</th>
                    <th>Comments</th>
                    <th>View Data</th>
                    <th>Lock Data</th>
                    <th>UnLock Data</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach($faculty as $row)
                    <tr>
                        <td>{{ $row->code }}</td>
                        <td style="text-transform: uppercase">{{ $row->name }}</td>
                        <td style="min-width:230px">
                            {{-- opens the comment page in a new tab, like the original "Add Comment" --}}
                            <form action="{{ route('pbas.hod.comment') }}" method="get" target="_blank">
                                <input type="hidden" name="username" value="{{ $row->id }}" />
                                <div class="form-group">
                                    <label>Year</label>
                                    <select name="year" class="form-control">
                                        @foreach($years as [$value, $label])
                                            <option value="{{ $value }}" @selected($value === $currentYear)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="text" name="hodcomment" class="form-control m-b-10" value="{{ $comments[$row->id] ?? 'No Comments Yet' }}" readonly />
                                <input class="btn btn-success" type="submit" value="Add Comment" name="view" />
                            </form>
                        </td>
                        <td><a class="btn btn-info" target="_blank" href="{{ route('pbas.report.show', $row->id) }}">View Data</a></td>
                        <td>
                            <form method="post" action="{{ route('pbas.hod.lock') }}">
                                @csrf
                                <input type="hidden" name="facultyid" value="{{ $row->id }}" />
                                <input type="hidden" name="facultyname" value="{{ $row->name }}" />
                                <input type="submit" name="lock" class="btn btn-warning" value="Lock" />
                            </form>
                        </td>
                        <td>
                            <form method="post" action="{{ route('pbas.hod.unlock') }}">
                                @csrf
                                <input type="hidden" name="facultyid" value="{{ $row->id }}" />
                                <input type="hidden" name="facultyname" value="{{ $row->name }}" />
                                <input type="submit" name="unlock" value="UnLock" class="btn btn-default" />
                            </form>
                        </td>
                        <td>
                            @if(isset($locks[$row->id]))
                                @if((int) $locks[$row->id] === 1)
                                    <strong>Data Already locked...</strong>
                                @else
                                    <strong class="text-danger">data is Not Locked Yet!!!</strong>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
