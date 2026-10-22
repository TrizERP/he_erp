@extends('pbas.layout')

@section('crumb', 'APPROVAL BY HOD')
@section('heading', 'LOCK/UNLOCK/COMMENT DATA')

@section('content')
    <div class="card">
        <h5 class="m-b-0">LOCK DATA-Comment</h5>
        <form name="frm_comment" method="post" action="{{ route('pbas.hod.comment.store') }}">
            @csrf
            <div class="form-group">
                <label>Write Your Comments here</label>
                <p>{{ $facultyId }}{{ $year }}</p>
                <textarea name="commentsta" rows="8" class="form-control"></textarea>
            </div>
            <input type="hidden" name="username" value="{{ $facultyId }}" />
            <input type="hidden" name="year" value="{{ $year }}" />
            <button type="submit" name="comment" value="comment" class="btn btn-success">Add Comment</button>
            <a href="{{ route('pbas.hod.index') }}" class="btn btn-default">Back to Lock Faculty Data</a>
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
