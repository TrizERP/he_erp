@extends('layouts.app')

@section('title', 'Internship Students')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manage Students for {{ $internship->title }}</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form action="{{ route('internships.students.store', $internship->id) }}" method="POST">
                                @csrf
                                <div class="form-row align-items-center">
                                    <div class="col-md-8">
                                        <select class="form-control" name="student_id" required>
                                            <option value="">Select Student</option>
                                            @foreach($allStudents as $student)
                                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary">Add Student</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            <tr>
                                <td>{{ $student->id }}</td>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $student->pivot->status == 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($student->pivot->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('internships.students.marks.index', ['internship' => $internship->id, 'student' => $student->id]) }}" class="btn btn-sm btn-info" title="Marks">
                                        <i class="fas fa-star"></i>
                                    </a>
                                    <a href="{{ route('internships.students.attendance.index', ['internship' => $internship->id, 'student' => $student->id]) }}" class="btn btn-sm btn-warning" title="Attendance">
                                        <i class="fas fa-calendar-check"></i>
                                    </a>
                                    <form action="{{ route('internships.students.update', ['internship' => $internship->id, 'student' => $student->id]) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="{{ $student->pivot->status == 'active' ? 'inactive' : 'active' }}">
                                        <button type="submit" class="btn btn-sm btn-{{ $student->pivot->status == 'active' ? 'warning' : 'success' }}" title="{{ $student->pivot->status == 'active' ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $student->pivot->status == 'active' ? 'times' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('internships.students.destroy', ['internship' => $internship->id, 'student' => $student->id]) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Remove">
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
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection