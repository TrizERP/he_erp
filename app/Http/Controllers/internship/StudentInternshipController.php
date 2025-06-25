<?php

namespace App\Http\Controllers\internship;

use Illuminate\Http\Request;
use App\Models\internship\InternshipStudent;
use App\Models\internship\InternshipAttendance;
use Illuminate\Support\Facades\Auth;

class StudentInternshipController extends Controller
{
    public function index()
    {
        $internships = Auth::user()->internships()->with('internship.company')->get();
        return view('student.internships.index', compact('internships'));
    }

    public function show(InternshipStudent $internship)
    {
        // Verify the student owns this internship
        if ($internship->student_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $attendance = $internship->attendance()->latest()->paginate(10);
        $marks = $internship->marks()->latest()->paginate(10);
        
        return view('student.internships.show', compact('internship', 'attendance', 'marks'));
    }

    public function markAttendance(Request $request, InternshipStudent $internship)
    {
        // Verify the student owns this internship
        if ($internship->student_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:present,late',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location_address' => 'required|string',
        ]);

        // Check if attendance for today already exists
        $today = now()->format('Y-m-d');
        if ($internship->attendance()->whereDate('date', $today)->exists()) {
            return redirect()->back()
                             ->with('error', 'Attendance for today already marked.');
        }

        InternshipAttendance::create([
            'internship_student_id' => $internship->id,
            'date' => $today,
            'status' => $request->status,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_address' => $request->location_address,
            'check_in' => now()->format('H:i:s'),
        ]);

        return redirect()->back()
                         ->with('success', 'Attendance marked successfully.');
    }

    public function markCheckOut(InternshipStudent $internship)
    {
        // Verify the student owns this internship
        if ($internship->student_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $today = now()->format('Y-m-d');
        $attendance = $internship->attendance()
                                ->whereDate('date', $today)
                                ->first();

        if (!$attendance) {
            return redirect()->back()
                             ->with('error', 'No attendance record found for today.');
        }

        if ($attendance->check_out) {
            return redirect()->back()
                             ->with('error', 'Check-out already marked for today.');
        }

        $attendance->update([
            'check_out' => now()->format('H:i:s'),
        ]);

        return redirect()->back()
                         ->with('success', 'Check-out marked successfully.');
    }
}