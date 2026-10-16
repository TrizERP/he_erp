<?php

namespace App\Http\Controllers\fees\fees_waiver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class fees_waiver_controller extends Controller
{
    // search a student by name / enrollment no / mobile and show the waiver form for each match
    public function index(Request $request)
    {
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $termId = session()->get('term_id');
        $term = trim((string) $request->input('search'));
        $students = collect();

        if ($term !== '') {
            $students = DB::table('tblstudent as ts')
                ->join('tblstudent_enrollment as se', function ($join) {
                    $join->on('se.student_id', '=', 'ts.id')->on('se.sub_institute_id', '=', 'ts.sub_institute_id');
                })
                ->leftJoin('standard as s', 's.id', '=', 'se.standard_id')
                ->leftJoin('division as d', 'd.id', '=', 'se.section_id')
                ->leftJoin('fees_waiver as fw', function ($join) use ($syear) {
                    $join->on('fw.student_id', '=', 'ts.id')
                        ->on('fw.sub_institute_id', '=', 'ts.sub_institute_id')
                        ->where('fw.syear', $syear);
                })
                ->where('ts.sub_institute_id', $sub_institute_id)
                ->where('se.syear', $syear)
                // a student can be enrolled in several terms of a year (SEM 3 and SEM 4): follow the term chosen in the header
                ->when($termId, function ($q) use ($termId) {
                    $q->where('s.marking_period_id', $termId);
                }, function ($q) use ($syear) {
                    $q->whereRaw('se.id = (select max(se2.id) from tblstudent_enrollment se2
                        where se2.student_id = ts.id and se2.sub_institute_id = ts.sub_institute_id and se2.syear = ?)', [$syear]);
                })
                ->where(function ($q) use ($term) {
                    $q->where('ts.enrollment_no', 'like', "%{$term}%")
                        ->orWhere('ts.mobile', 'like', "%{$term}%")
                        ->orWhere('ts.first_name', 'like', "%{$term}%")
                        ->orWhere('ts.middle_name', 'like', "%{$term}%")
                        ->orWhere('ts.last_name', 'like', "%{$term}%");
                })
                ->selectRaw("ts.id, ts.enrollment_no, ts.first_name, ts.middle_name, ts.last_name,
                    s.name as standard_name, d.name as division_name, fw.amount, fw.remarks, fw.applied_at")
                ->orderBy('ts.first_name')
                ->limit(50)
                ->get();
        }

        $data = [
            'students' => $students,
            'search' => $term,
            'status_code' => session()->get('status_code'),
            'message' => session()->get('message'),
        ];

        return view('fees.fees_waiver.index', ['data' => $data]);
    }

    // save (or replace) the waiver of one student for the current year
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer',
            'amount.*' => 'required|numeric|min:0',
            'remarks.*' => 'nullable|string|max:255',
        ]);
        $studentId = (int) $request->student_id;
        $amount = $request->input("amount.{$studentId}", 0);
        $remarks = $request->input("remarks.{$studentId}");

        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');

        $exists = DB::table('tblstudent')
            ->where(['id' => $studentId, 'sub_institute_id' => $sub_institute_id])->exists();
        if (! $exists) {
            return back()->with(['status_code' => 0, 'message' => 'Student not found']);
        }

        $key = ['sub_institute_id' => $sub_institute_id, 'syear' => $syear, 'student_id' => $studentId];
        $values = [
            'amount' => $amount,
            'remarks' => $remarks,
            'created_by' => session()->get('user_id'),
            'applied_at' => null,
            'updated_at' => now(),
        ];
        if (DB::table('fees_waiver')->where($key)->exists()) {
            DB::table('fees_waiver')->where($key)->update($values);
        } else {
            DB::table('fees_waiver')->insert($key + $values + ['created_at' => now()]);
        }

        return redirect()->route('fees_waiver.index', ['search' => $request->search])
            ->with(['status_code' => 1, 'message' => 'Fees Waiver saved']);
    }
}
