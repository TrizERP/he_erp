<?php

namespace App\Http\Controllers\result\SubjectWiseGradeMaster;

use App\Http\Controllers\Controller;
use App\Models\GradeSubjectWiseMaster;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function App\Helpers\is_mobile;

class SubjectWiseGradeController extends Controller
{
    public function index()
    {   
        $data = DB::table('grade_subject_wise_master as gsm')
        ->leftJoin('subject as s', 's.id', '=', 'gsm.subject')
        ->select(
            'gsm.*',
            's.subject_name as subject_name'
        )
        ->orderBy('gsm.id', 'DESC')
        ->whereNull('gsm.deleted_at')
        ->get();

        return view('result.grade-subject.index', compact('data'));
    }

    public function create()
    {
        return view('result.grade-subject.create');
    }

    public function store(Request $request)
    {   
        $request->validate([
            'standard_id'      => $request->get('standard'),
            'grade_id'      => $request->get('grade'),
            'subject' => 'required',
            'title.*' => 'required|string',
            'breakoff.*' => 'nullable|integer',
            'sort_order.*' => 'nullable|integer',
            'syear' => 'required',
            'sub_institute_id' => 'required',
            'term_id' => 'required',
        ]);

        foreach ($request->title as $index => $title) {

            DB::table('grade_subject_wise_master')->insert([
                'standard_id'      => $request->get('standard'),
                'grade_id'      => $request->get('grade'),
                'subject' => $request->subject,
                'title' => $title,
                'breakoff' => $request->breakoff[$index] ?? null,
                'sort_order' => $request->sort_order[$index] ?? null,
                'syear' => $request->syear,
                'term_id' => $request->term_id,
                'sub_institute_id' => $request->sub_institute_id,
                'created_at' => now(),
            ]);
        }

        return redirect()->route('grade-subject.index')
                        ->with('success', 'Record added successfully');
    }


    public function edit($id)
    {
        $item = DB::table('grade_subject_wise_master as gsm')
            ->leftJoin('subject as s', 's.id', '=', 'gsm.subject')
            ->select(
                'gsm.*',
                's.subject_name as subject_name'
            )
            ->where('gsm.id', $id)
            ->first();
        return view('result.grade-subject.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required',
            'title.0' => 'required',
            'breakoff.0' => 'nullable|integer',
            'sort_order.0' => 'nullable|integer',
            'syear' => 'required',
            'sub_institute_id' => 'required',
            'term_id' => 'required',
        ]);

        $item = GradeSubjectWiseMaster::findOrFail($id);

        $item->update([
            'subject' => $request->subject,
            'title' => $request->title[0],
            'breakoff' => $request->breakoff[0],
            'sort_order' => $request->sort_order[0],
            'syear' => $request->syear,
            'sub_institute_id' => $request->sub_institute_id,
            'term_id' => $request->term_id,
            'updated_at' => now(),
            
        ]);

        return redirect()->route('grade-subject.index')
            ->with('success', 'Record updated successfully');
    }



    public function destroy($id)
    {
        DB::table('grade_subject_wise_master')
            ->where('id', $id)
            ->update([
                'deleted_at' => now()
            ]);

        return redirect()->route('grade-subject.index')
            ->with('success', 'Record deleted successfully');
    }
}