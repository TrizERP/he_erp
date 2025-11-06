@extends('layouts.app')

@section('title', 'Edit Internship Marks')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Marks for {{ $internshipStudent->student->name }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('internships.students.marks.update', ['internship' => $internshipStudent->internship_id, 'student' => $internshipStudent->student_id, 'mark' => $mark->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="marks">Marks</label>
                            <input type="number" class="form-control" id="marks" name="marks" value="{{ $mark->marks }}" min="0" max="100" required>
                        </div>
                        <div class="form-group">
                            <label for="comments">Comments</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3">{{ $mark->comments }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection