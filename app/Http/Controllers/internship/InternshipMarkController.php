<?php

namespace App\Http\Controllers\internship;

use App\Models\internship\InternshipStudent;
use App\Models\internship\InternshipMark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternshipMarkController extends Controller
{
    public function index(InternshipStudent $internshipStudent)
    {
        $marks = $internshipStudent->marks()->latest()->paginate(10);
        return view('internships.marks.index', compact('internshipStudent', 'marks'));
    }

    public function create(InternshipStudent $internshipStudent)
    {
        return view('internships.marks.create', compact('internshipStudent'));
    }

    public function store(Request $request, InternshipStudent $internshipStudent)
    {
        $request->validate([
            'marks' => 'required|integer|min:0|max:100',
            'comments' => 'nullable|string',
        ]);

        InternshipMark::create([
            'internship_student_id' => $internshipStudent->id,
            'marks' => $request->marks,
            'comments' => $request->comments,
            'evaluated_by' => Auth::id(),
        ]);

        return redirect()->route('internships.students.marks.index', $internshipStudent)
                         ->with('success', 'Marks added successfully.');
    }

    public function edit(InternshipStudent $internshipStudent, InternshipMark $mark)
    {
        return view('internships.marks.edit', compact('internshipStudent', 'mark'));
    }

    public function update(Request $request, InternshipStudent $internshipStudent, InternshipMark $mark)
    {
        $request->validate([
            'marks' => 'required|integer|min:0|max:100',
            'comments' => 'nullable|string',
        ]);

        $mark->update([
            'marks' => $request->marks,
            'comments' => $request->comments,
        ]);

        return redirect()->route('internships.students.marks.index', $internshipStudent)
                         ->with('success', 'Marks updated successfully.');
    }

    public function destroy(InternshipStudent $internshipStudent, InternshipMark $mark)
    {
        $mark->delete();

        return redirect()->route('internships.students.marks.index', $internshipStudent)
                         ->with('success', 'Marks deleted successfully.');
    }
}