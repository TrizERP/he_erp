@extends('pbas.layout')

@section('crumb', 'Annual Confidential Report')
@section('heading', 'Insert/Update Data of  ACR for : '.$facultyName)

@section('content')
    <div class="card">
        <h5 class="m-b-0">ACR Entry for : {{ $facultyName }}</h5>
        <form name="frm_acr" method="post" action="{{ route('pbas.principal.acr.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                    <label>Year</label>
                    <input type="text" class="form-control" value="{{ $year }}" disabled />
                </div>
                @foreach([
                    'punctuality' => 'Punctuality',
                    'innovative' => 'Innovative Approach',
                    'dedication' => 'Committed to work',
                    'inp' => 'Obedient',
                    'performance' => '5. Overall Performance',
                ] as $name => $label)
                    <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                        <label>{{ $label }}</label>
                        <input type="text" name="{{ $name }}" class="form-control" placeholder="Enter Marks in number"
                               value="{{ old($name, $acr->{$name} ?? '') }}" />
                    </div>
                @endforeach
            </div>
            <input type="hidden" name="facultyid" value="{{ $facultyId }}" />
            <input type="hidden" name="moveback" value="{{ $moveback }}" />
            <button type="submit" name="comment" value="comment" class="btn btn-success">Add/Update ACR Marks</button>
            <a href="{{ route('pbas.principal.index', ['e' => $moveback]) }}" class="btn btn-default">Move Back to Faculty List</a>
        </form>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr><th>Sr. No.</th><th>Faculty id</th><th>Year</th><th>Comments Given</th></tr>
                </thead>
                <tbody>
                @foreach($comments as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row->user_id }}</td>
                        <td>{{ $row->year }}</td>
                        <td>{{ $row->comment }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
