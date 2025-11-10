@extends('layouts.app')

@section('title', 'Add Internship Attendance')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Attendance for {{ $internshipStudent->student->name }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('internships.students.attendance.store', ['internship' => $internshipStudent->internship_id, 'student' => $internshipStudent->student_id]) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="holiday">Holiday</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="check_in">Check In Time</label>
                            <input type="time" class="form-control" id="check_in" name="check_in">
                        </div>
                        <div class="form-group">
                            <label for="check_out">Check Out Time</label>
                            <input type="time" class="form-control" id="check_out" name="check_out">
                        </div>
                        <div class="form-group">
                            <label for="latitude">Latitude</label>
                            <input type="number" step="any" class="form-control" id="latitude" name="latitude">
                        </div>
                        <div class="form-group">
                            <label for="longitude">Longitude</label>
                            <input type="number" step="any" class="form-control" id="longitude" name="longitude">
                        </div>
                        <div class="form-group">
                            <label for="location_address">Location Address</label>
                            <input type="text" class="form-control" id="location_address" name="location_address">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection