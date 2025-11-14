<?php

namespace App\Http\Controllers\internship;

use App\Models\internship\Internship;
use App\Models\User;
use App\Models\internship\InternshipStudent;
use Illuminate\Http\Request;

class InternshipStudentController extends Controller
{
    public function index(Internship $internship)
    {
        $students = $internship->students()->paginate(10);
        $allStudents = User::where('role', 'student')->get();
        
        return view('internships.students.index', compact('internship', 'students', 'allStudents'));
    }

    public function store(Request $request, Internship $internship)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        // Check if student is already assigned
        if ($internship->students()->where('student_id', $request->student_id)->exists()) {
            return redirect()->back()
                             ->with('error', 'Student is already assigned to this internship.');
        }

        $internship->students()->attach($request->student_id, ['status' => 'active']);

        return redirect()->route('internships.students.index', $internship)
                         ->with('success', 'Student assigned successfully.');
    }

    public function update(Request $request, Internship $internship, User $student)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,completed',
            'feedback' => 'nullable|string',
        ]);

        $internship->students()->updateExistingPivot($student->id, [
            'status' => $request->status,
            'feedback' => $request->feedback,
        ]);

        return redirect()->route('internships.students.index', $internship)
                         ->with('success', 'Student status updated successfully.');
    }

    public function destroy(Internship $internship, User $student)
    {
        $internship->students()->detach($student->id);

        return redirect()->route('internships.students.index', $internship)
                         ->with('success', 'Student removed from internship successfully.');
    }
}