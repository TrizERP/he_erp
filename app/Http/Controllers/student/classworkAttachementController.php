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
    $studentId = $id ?? request()->query('student_id');

    if ($studentId) {
        $documents = studentAchievementModel::where('student_id', $studentId)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->toArray();
    } else {
        $documents = []; // prefer empty instead of all students if you want student-specific listing
    }

    return view('student.classwork_view', [
        'data' => ['studentachievement' => $documents],
        'student_data' => ['id' => $studentId]
    ]);
}

    public function store(Request $request)
{
    $request->validate([
        'student_id'      => 'required',
        'document_type'   => 'required', 
        'document_title'   => 'required',
        'description'      => 'required',
        'file_name'        => 'required|file',
        'sub_institute_id' => 'nullable|integer'
    ]);

    try {

        // File Upload
        $fileName = null;
        if ($request->hasFile('file_name')) {
            $file = $request->file('file_name');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('student_document', $fileName);
        }

        // Insert into DB
        \App\Models\student\studentAchievementModel::create([
            'student_id'      => $request->student_id,
           'document_type'    => $request->document_type,
            'title'            => $request->document_title,
            'description'      => $request->description,
            'file_path'        => $fileName,
            'created_on'       => date('Y-m-d H:i:s'),
            'sub_institute_id' => session()->get('sub_institute_id'),
            'created_by'       => session()->get('user_id'),
        ]);

        return back()->with('success', 'Document uploaded successfully');

    } catch (\Exception $e) {

        return back()->with('error', $e->getMessage());
    }
}

}