<?php

namespace App\Http\Controllers\admission;

use App\Exports\Admission\ReportExport;
use App\Services\Admission\AdmissionException;
use App\Services\Admission\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Workflow reports (funnel, status, documents, eligibility, offers, fees, confirmed). The legacy
 * admissionReportController keeps the original five reports; these cover the new workflow data.
 * The export runs the very same scoped query as the screen.
 */
class AdmissionProcessReportController extends AdmissionController
{
    public function __construct(protected ReportService $reports)
    {
    }

    public function index(Request $request)
    {
        $type = $request->input('type');
        $filters = $request->only(['standard_id', 'from', 'to']);
        $report = null;
        if ($type) {
            try {
                $report = $this->reports->build($type, $this->instituteId(), $this->syear(), $filters);
            } catch (AdmissionException $e) {
                return $this->flash(0, $e->getMessage());
            }
        }

        return view('admission.process.reports', ['data' => [
            'types'     => ReportService::TYPES,
            'type'      => $type,
            'report'    => $report,
            'filters'   => $filters,
            'standards' => DB::table('standard')->where('sub_institute_id', $this->instituteId())->orderBy('name')->get(['id', 'name']),
        ]]);
    }

    public function export(Request $request, string $type)
    {
        try {
            $report = $this->reports->build($type, $this->instituteId(), $this->syear(), $request->only(['standard_id', 'from', 'to']));
        } catch (AdmissionException $e) {
            return $this->flash(0, $e->getMessage());
        }

        return Excel::download(new ReportExport($report['title'], $report['headings'], $report['rows']), 'admission_'.$type.'_'.date('Ymd').'.xlsx');
    }
}
