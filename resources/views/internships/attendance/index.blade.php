@extends('layouts.app')

@section('title', 'Internship Attendance')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Attendance for {{ $internshipStudent->student->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('internships.students.attendance.create', ['internship' => $internshipStudent->internship_id, 'student' => $internshipStudent->student_id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Attendance
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->id }}</td>
                                <td>{{ $attendance->date->format('d M Y') }}</td>
                                <td>
                                    @if($attendance->status == 'present')
                                        <span class="badge bg-success">Present</span>
                                    @elseif($attendance->status == 'absent')
                                        <span class="badge bg-danger">Absent</span>
                                    @elseif($attendance->status == 'late')
                                        <span class="badge bg-warning">Late</span>
                                    @else
                                        <span class="badge bg-info">Holiday</span>
                                    @endif
                                </td>
                                <td>{{ $attendance->check_in }}</td>
                                <td>{{ $attendance->check_out }}</td>
                                <td>
                                    @if($attendance->latitude && $attendance->longitude)
                                        <a href="https://maps.google.com/?q={{ $attendance->latitude }},{{ $attendance->longitude }}" target="_blank">
                                            View Map
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('internships.students.attendance.edit', ['internship' => $internshipStudent->internship_id, 'student' => $internshipStudent->student_id, 'attendance' => $attendance->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('internships.students.attendance.destroy', ['internship' => $internshipStudent->internship_id, 'student' => $internshipStudent->student_id, 'attendance' => $attendance->id]) }}" method="POST" style="display: inline-block;">
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
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection