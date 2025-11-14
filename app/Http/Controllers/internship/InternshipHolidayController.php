<?php

namespace App\Http\Controllers\internship;

use App\Models\internship\Internship;
use App\Models\internship\InternshipHoliday;
use Illuminate\Http\Request;

class InternshipHolidayController extends Controller
{
    public function index(Internship $internship)
    {
        $holidays = $internship->holidays()->latest()->paginate(10);
        return view('internships.holidays.index', compact('internship', 'holidays'));
    }

    public function create(Internship $internship)
    {
        return view('internships.holidays.create', compact('internship'));
    }

    public function store(Request $request, Internship $internship)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $internship->holidays()->create($request->all());

        return redirect()->route('internships.holidays.index', $internship)
                         ->with('success', 'Holiday created successfully.');
    }

    public function edit(Internship $internship, InternshipHoliday $holiday)
    {
        return view('internships.holidays.edit', compact('internship', 'holiday'));
    }

    public function update(Request $request, Internship $internship, InternshipHoliday $holiday)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $holiday->update($request->all());

        return redirect()->route('internships.holidays.index', $internship)
                         ->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Internship $internship, InternshipHoliday $holiday)
    {
        $holiday->delete();

        return redirect()->route('internships.holidays.index', $internship)
                         ->with('success', 'Holiday deleted successfully.');
    }
}