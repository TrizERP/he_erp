<?php

namespace App\Http\Controllers\fees\fees_waiver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class fees_waiver_report_controller extends Controller
{
    // report of every fees waiver of the selected year, with filters and a CSV export
    public function index(Request $request)
    {
        $rows = $this->rows($request);

        if ($request->input('export') === 'csv') {
            return $this->csv($rows);
        }

        return view('fees.fees_waiver.report', ['data' => [
            'rows' => $rows,
            'total' => $rows->sum('amount'),
            'filters' => $request->only(['search', 'status', 'from_date', 'to_date']),
        ]]);
    }

    protected function rows(Request $request)
    {
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $termId = session()->get('term_id');
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        return DB::table('fees_waiver as fw')
            ->join('tblstudent as ts', function ($join) {
                $join->on('ts.id', '=', 'fw.student_id')->on('ts.sub_institute_id', '=', 'fw.sub_institute_id');
            })
            ->leftJoin('tblstudent_enrollment as se', function ($join) use ($syear) {
                $join->on('se.student_id', '=', 'ts.id')
                    ->on('se.sub_institute_id', '=', 'ts.sub_institute_id')
                    ->where('se.syear', $syear);
            })
            ->leftJoin('standard as s', function ($join) use ($termId) {
                $join->on('s.id', '=', 'se.standard_id');
                if ($termId) {
                    $join->where('s.marking_period_id', $termId);
                }
            })
            ->leftJoin('division as d', 'd.id', '=', 'se.section_id')
            ->leftJoin('tbluser as u', 'u.id', '=', 'fw.created_by')
            ->where('fw.sub_institute_id', $sub_institute_id)
            ->where('fw.syear', $syear)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('ts.enrollment_no', 'like', "%{$search}%")
                        ->orWhere('ts.mobile', 'like', "%{$search}%")
                        ->orWhere('ts.first_name', 'like', "%{$search}%")
                        ->orWhere('ts.middle_name', 'like', "%{$search}%")
                        ->orWhere('ts.last_name', 'like', "%{$search}%");
                });
            })
            ->when($status === 'pending', fn ($q) => $q->whereNull('fw.applied_at'))
            ->when($status === 'applied', fn ($q) => $q->whereNotNull('fw.applied_at'))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('fw.updated_at', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('fw.updated_at', '<=', $request->to_date))
            // one line per student: the enrollment of the selected term (or the latest one when no term is chosen)
            ->when($termId, fn ($q) => $q->whereNotNull('s.id'), function ($q) use ($syear) {
                $q->whereRaw('(se.id is null or se.id = (select max(se2.id) from tblstudent_enrollment se2
                    where se2.student_id = ts.id and se2.sub_institute_id = ts.sub_institute_id and se2.syear = ?))', [$syear]);
            })
            ->selectRaw("fw.id, ts.enrollment_no, ts.first_name, ts.middle_name, ts.last_name, s.name as standard_name,
                d.name as division_name, fw.amount, fw.remarks, fw.applied_at, fw.updated_at,
                CONCAT_WS(' ', u.first_name, u.last_name) as created_by_name")
            ->orderBy('fw.updated_at', 'desc')
            ->get();
    }

    protected function csv($rows)
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['Sr', 'Enrollment No', 'Student', 'Class', 'Fees Waiver', 'Remarks', 'Status', 'Updated By', 'Updated On']);
        foreach ($rows as $i => $r) {
            fputcsv($out, [
                $i + 1,
                $r->enrollment_no,
                trim($r->first_name.' '.$r->middle_name.' '.$r->last_name),
                trim($r->standard_name.' '.$r->division_name),
                $r->amount,
                $r->remarks,
                $r->applied_at ? 'Applied' : 'Pending',
                $r->created_by_name,
                date('d-m-Y', strtotime($r->updated_at)),
            ]);
        }
        fputcsv($out, ['', '', '', 'Total', $rows->sum('amount')]);
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fees_waiver_report.csv"',
        ]);
    }
}
