<?php

namespace App\Http\Controllers\pbas;

use App\Http\Controllers\Controller;
use App\Services\Pbas\PbasAccess;
use App\Services\Pbas\PbasReportService;

/**
 * "Your PBAS Summary > API Form": the printable Annexure-B self-assessment (original pbas_facl.php).
 * HOD / Principal open the same form for a faculty member from their lock / ACR lists.
 */
class PbasReportController extends Controller
{
    public function index(PbasReportService $reports)
    {
        return $this->render($reports, PbasAccess::userId());
    }

    public function show(PbasReportService $reports, int $user)
    {
        abort_unless(PbasAccess::canManage($user), 403, 'You are not authorised to view this faculty member\'s PBAS data.');

        return $this->render($reports, $user);
    }

    protected function render(PbasReportService $reports, int $userId)
    {
        $report = $reports->build(PbasAccess::instituteId(), $userId);

        if ($report['profile'] === null) {
            // the original printed an empty form here; ask for Part A first instead
            if ($userId !== PbasAccess::userId()) {
                abort(404, 'This faculty member has not saved General Information (Part A) yet, so there is no API Form to show.');
            }

            return redirect()->route('pbas.profile.index')
                ->with('pbas_error', 'General Information (Part A) has not been saved yet, so the API Form cannot be generated.');
        }

        return view('pbas.report', [
            'report' => $report,
            'syear' => (int) (session()->get('syear') ?: date('Y')),
        ]);
    }
}
