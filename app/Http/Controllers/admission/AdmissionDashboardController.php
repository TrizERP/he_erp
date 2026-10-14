<?php

namespace App\Http\Controllers\admission;

use App\Services\Admission\ApplicationQuery;
use App\Services\Admission\DashboardService;
use Illuminate\Http\Request;

/** Admission dashboard: metric tiles and the filtered list behind each tile (same query for both). */
class AdmissionDashboardController extends AdmissionController
{
    public function __construct(protected DashboardService $dashboard)
    {
    }

    public function index()
    {
        $res = $this->dashboard->summary($this->instituteId(), $this->syear());
        $res['status_code'] = 1;
        $res['message'] = 'Success';

        return view('admission.process.dashboard', ['data' => $res]);
    }

    public function drill(Request $request, string $metric)
    {
        abort_unless(array_key_exists($metric, ApplicationQuery::METRICS), 404);

        $res = [
            'title'    => ApplicationQuery::METRICS[$metric],
            'rows'     => $this->dashboard->drillDown($this->instituteId(), $this->syear(), $metric, $request->only('q')),
            'filters'  => $request->only('q'),
            'metric'   => $metric,
        ];

        return view('admission.process.list', ['data' => $res]);
    }
}
