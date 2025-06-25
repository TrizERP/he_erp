<?php

namespace App\Http\Controllers\internship;

use App\Http\Controllers\Controller;
use App\Models\internship\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::latest()->paginate(10);
        return view('internship.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('internship.companies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string|max:20',
            'requirements' => 'nullable|string',
        ]);

        Company::create($request->all());

        return redirect()->route('internship.companies.index')
                         ->with('success', 'Company created successfully.');
    }

    public function show(Company $company)
    {
        return view('internship.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('internship.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string|max:20',
            'requirements' => 'nullable|string',
        ]);

        $company->update($request->all());

        return redirect()->route('internship.companies.index')
                         ->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('internship.companies.index')
                         ->with('success', 'Company deleted successfully.');
    }
}