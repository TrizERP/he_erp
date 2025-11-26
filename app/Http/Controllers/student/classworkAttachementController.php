<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\student\studentAchievementModel;
use Illuminate\Support\Facades\File;

class classworkAttachementController extends Controller
{
    /**
     * Show student achievements in the existing blade view.
     *
     * URL example: /student/{id}/achievements
     *
     * @param  int  $student_id
     * @return \Illuminate\View\View
     */



 public function index($id = null)
{
    // If $id given, filter by student_id, else show all
    $studentId = $id ?? request()->query('student_id');

    if ($studentId) {
        $documents = studentAchievementModel::where('student_id', $studentId)
                    ->orderBy('created_at', 'desc')->get()->toArray();
    } else {
        $documents = studentAchievementModel::orderBy('created_at', 'desc')->get()->toArray();
    }

    // pass student_data if blade expects it
    $student_data = ['id' => $studentId];

    return view('student.classwork_view', [
        'data' => ['studentachievement' => $documents],
        'student_data' => $student_data
    ]);
}

public function store(Request $request, $id = null)
{
    // minimal validation
    $request->validate([
        'student_id' => 'required|integer',
        'document_title' => 'required|string|max:255',
        'document_type_id' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'file_name' => 'nullable|file|max:10240'
    ]);

    try {
        $achievement = new \App\Models\student\classworkAttachementModel();

        // explicit assignments (no mass assignment problems)
        $achievement->student_id = $request->input('student_id');
        $achievement->title = $request->input('document_title');
        $achievement->document_type = $request->input('document_type_id');
        $achievement->description = $request->input('description');

        // file: store in storage/app/public/student_document and save filename
        if ($request->hasFile('file_name')) {
            $file = $request->file('file_name');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $file->storeAs('student_document', $filename, 'public');
            // Blade uses: asset('storage/student_document/' . $docdata['file_path'])
            $achievement->file_path = $filename;
        }

        $achievement->save();

        // After save, redirect to the index route so the view reloads with fresh data
        return redirect()->route('student.achievements.index', ['id' => $achievement->student_id])
                         ->with('success', 'Achievement saved.');

    } catch (\Exception $e) {
        Log::error('Achievement store error: '.$e->getMessage().' -- '.$e->getTraceAsString());
        return redirect()->back()->with('error', 'Failed to save achievement. Check logs.');
    }
}
}