<?php

namespace App\Http\Controllers\internship;

use App\Models\internship\InternshipStudent;
use App\Models\internship\InternshipAttendance;
use Illuminate\Http\Request;

class InternshipAttendanceController extends Controller
{
    public function index(InternshipStudent $internshipStudent)
    {
        $attendances = $internshipStudent->attendance()->latest()->paginate(10);
        return view('internships.attendance.index', compact('internshipStudent', 'attendances'));
    }

    public function create(InternshipStudent $internshipStudent)
    {
        return view('internships.attendance.create', compact('internshipStudent'));
    }

    public function store(Request $request, InternshipStudent $internshipStudent)
    {
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,holiday',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_address' => 'nullable|string',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
        ]);

        InternshipAttendance::create([
            'internship_student_id' => $internshipStudent->id,
            'date' => $request->date,
            'status' => $request->status,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_address' => $request->location_address,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
        ]);

        return redirect()->route('internships.students.attendance.index', $internshipStudent)
                         ->with('success', 'Attendance recorded successfully.');
    }

    public function edit(InternshipStudent $internshipStudent, InternshipAttendance $attendance)
    {
        return view('internships.attendance.edit', compact('internshipStudent', 'attendance'));
    }

    public function update(Request $request, InternshipStudent $internshipStudent, InternshipAttendance $attendance)
    {
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,holiday',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_address' => 'nullable|string',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
        ]);

        $attendance->update($request->all());

        return redirect()->route('internships.students.attendance.index', $internshipStudent)
                         ->with('success', 'Attendance updated successfully.');
    }

    public function destroy(InternshipStudent $internshipStudent, InternshipAttendance $attendance)
    {
        $attendance->delete();

        return redirect()->route('internships.students.attendance.index', $internshipStudent)
                         ->with('success', 'Attendance deleted successfully.');
    }
}