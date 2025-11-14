<?php

namespace App\Http\Controllers\internship;

use App\Http\Controllers\Controller;
use App\Models\internship\Internship;
use App\Models\internship\Company;
use Illuminate\Http\Request;

class InternshipController extends Controller
{
    public function index()
    {
        $internships = Internship::with('company')->latest()->paginate(10);
        return view('internships.index', compact('internships'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('internships.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'company_id' => 'required|exists:internship_companies,id',
        ]);

        Internship::create($request->all());

        return redirect()->route('internships.index')
                         ->with('success', 'Internship created successfully.');
    }

    public function show(Internship $internship)
    {
        return view('internships.show', compact('internship'));
    }

    public function edit(Internship $internship)
    {
        $companies = Company::all();
        return view('internships.edit', compact('internship', 'companies'));
    }

    public function update(Request $request, Internship $internship)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'company_id' => 'required|exists:internship_companies,id',
            'is_active' => 'boolean',
        ]);

        $internship->update($request->all());

        return redirect()->route('internships.index')
                         ->with('success', 'Internship updated successfully.');
    }

    public function destroy(Internship $internship)
    {
        $internship->delete();

        return redirect()->route('internships.index')
                         ->with('success', 'Internship deleted successfully.');
    }
}