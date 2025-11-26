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
    public function showStudentAchievements($student_id)
    {
        // Fetch achievement rows for this student (adjust column name if different)
        $documents = studentAchievementModel::where('student_id', $student_id)
                        ->orderBy('created_on', 'desc') // optional
                        ->get()
                        ->toArray();

        // Keep the keys your blade currently checks so we don't need to change the view
        $data = [
            'studentachievement'  => $documents,
            '$studentachievement' => $documents,   // literal key to satisfy the blade's existing typo check
            'data'                => true          // ensures @if(isset($data['data'])) passes
        ];

        $student_data = ['id' => $student_id];

        return view('student.classwork_view', [
            'data' => $data,
            'student_data' => $student_data
        ]);
    }
}
