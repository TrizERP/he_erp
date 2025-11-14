@extends('layouts.app')

@section('title', 'Internship Marks')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Marks for {{ $internshipStudent->student->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('internships.students.marks.create', ['internship' => $internshipStudent->internship_id, 'student' => $internshipStudent->student_id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Marks
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Marks</th>
                                <th>Comments</th>
                                <th>Evaluated By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($marks as $mark)
                            <tr>
                                <td>{{ $mark->id }}</td>
                                <td>{{ $mark->marks }}</td>
                                <td>{{ $mark->comments }}</td>
                                <td>{{ $mark->evaluator->name }}</td>
                                <td>{{ $mark->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('internships.students.marks.edit', ['internship' => $internshipStudent->internship_id, 'student' => $internshipStudent->student_id, 'mark' => $mark->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('internships.students.marks.destroy', ['internship' => $internshipStudent->internship_id, 'student' => $internshipStudent->student_id, 'mark' => $mark->id]) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $marks->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection