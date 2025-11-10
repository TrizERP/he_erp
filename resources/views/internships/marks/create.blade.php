@extends('layouts.app')

@section('title', 'Add Internship Marks')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Marks for {{ $internshipStudent->student->name }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('internships.students.marks.store', ['internship' => $internshipStudent->internship_id, 'student' => $internshipStudent->student_id]) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="marks">Marks</label>
                            <input type="number" class="form-control" id="marks" name="marks" min="0" max="100" required>
                        </div>
                        <div class="form-group">
                            <label for="comments">Comments</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection