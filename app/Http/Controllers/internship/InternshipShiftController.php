<?php

namespace App\Http\Controllers\internship;

use App\Models\internship\Internship;
use App\Models\internship\InternshipShift;
use Illuminate\Http\Request;

class InternshipShiftController extends Controller
{
    public function index(Internship $internship)
    {
        $shifts = $internship->shifts()->latest()->paginate(10);
        return view('internships.shifts.index', compact('internship', 'shifts'));
    }

    public function create(Internship $internship)
    {
        return view('internships.shifts.create', compact('internship'));
    }

    public function store(Request $request, Internship $internship)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $internship->shifts()->create($request->all());

        return redirect()->route('internships.shifts.index', $internship)
                         ->with('success', 'Shift created successfully.');
    }

    public function edit(Internship $internship, InternshipShift $shift)
    {
        return view('internships.shifts.edit', compact('internship', 'shift'));
    }

    public function update(Request $request, Internship $internship, InternshipShift $shift)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $shift->update($request->all());

        return redirect()->route('internships.shifts.index', $internship)
                         ->with('success', 'Shift updated successfully.');
    }

    public function destroy(Internship $internship, InternshipShift $shift)
    {
        $shift->delete();

        return redirect()->route('internships.shifts.index', $internship)
                         ->with('success', 'Shift deleted successfully.');
    }
}