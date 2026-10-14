<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Models\placement\PlacementSeason;
use App\Services\Placement\DashboardService;
use App\Services\Placement\PlacementAccess;
use App\Services\Placement\StudentLookup;
use Illuminate\Http\Request;
use function App\Helpers\is_mobile;

/** Section 5: TPO, coordinator, student and principal dashboards (one route, chosen by role). */
class PlacementDashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboard, StudentLookup $students)
    {
        $role = PlacementAccess::role();
        $seasons = PlacementSeason::orderByDesc('start_date')->pluck('name', 'id');
        $seasonId = $request->filled('season_id') ? (int) $request->season_id : $dashboard->currentSeasonId();

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['seasons'] = $seasons;
        $res['season_id'] = $seasonId;

        if ($role === PlacementAccess::STUDENT) {
            $res['view'] = 'student';
            $res['student'] = $dashboard->student((int) session('user_id'));
        } elseif ($role === PlacementAccess::COORDINATOR) {
            $scope = $students->scopeStandardIds() ?? [];
            $res['view'] = 'coordinator';
            $res['kpi'] = $dashboard->management($seasonId, $scope);
            $res['actions'] = $dashboard->coordinatorActions($scope);
        } else {
            // TPO / admin default to operations; the principal defaults to analytics. Either can switch.
            $res['view'] = $request->input('view', $role === PlacementAccess::PRINCIPAL ? 'principal' : 'management');
            $res['view'] = in_array($res['view'], ['management', 'principal'], true) ? $res['view'] : 'management';
            if ($res['view'] === 'principal') {
                $res['analytics'] = $dashboard->principal($seasonId);
            } else {
                $res['kpi'] = $dashboard->management($seasonId);
            }
        }

        return is_mobile($request->input('type'), 'placement/dashboard/index', $res, 'view');
    }
}
