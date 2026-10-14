<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Http\Requests\placement\PlacementRoundRequest;
use App\Http\Requests\placement\PlacementRoundResultsRequest;
use App\Http\Requests\placement\PlacementSlotRequest;
use App\Models\placement\PlacementDrive;
use App\Models\placement\PlacementDriveRound;
use App\Models\placement\PlacementMaster;
use App\Models\placement\PlacementRoundResult;
use App\Models\placement\PlacementRoundSlot;
use App\Services\Placement\RoundService;
use App\Services\Placement\SlotService;
use App\Services\Placement\StudentLookup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;

/** Section 3F: configurable rounds per drive, attendance, results, interview slots, result publishing. */
class PlacementRoundController extends Controller
{
    protected RoundService $rounds;
    protected SlotService $slots;
    protected StudentLookup $students;

    public function __construct(RoundService $rounds, SlotService $slots, StudentLookup $students)
    {
        $this->rounds = $rounds;
        $this->slots = $slots;
        $this->students = $students;
    }

    public function index(Request $request, $drive)
    {
        $drive = PlacementDrive::with('company')->findOrFail($drive);

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['drive'] = $drive;
        $res['rounds'] = PlacementDriveRound::withCount('results')->where('drive_id', $drive->id)->orderBy('sequence')->get();
        $res['round_types'] = PlacementMaster::where('master_type', 'round_type')->where('status', 1)->orderBy('sort_order')->pluck('name', 'id');

        return is_mobile($request->input('type'), 'placement/rounds/index', $res, 'view');
    }

    public function store(PlacementRoundRequest $request, $drive)
    {
        $drive = PlacementDrive::findOrFail($drive);
        $this->rounds->create($drive, $request->validated() + ['is_final' => $request->boolean('is_final') ? 1 : 0]);

        return redirect()->route('placement.rounds.index', $drive->id)->with('data', ['status_code' => 1, 'message' => 'Round added.']);
    }

    public function show(Request $request, $round)
    {
        $round = PlacementDriveRound::with('drive.company')->findOrFail($round);
        $scope = $this->students->scopeStandardIds();

        $rows = DB::table('placement_round_result as rr')
            ->join('tblstudent as s', 's.id', '=', 'rr.student_id')
            ->leftJoin('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', session('syear')))
            ->leftJoin('standard as st', 'st.id', '=', 'e.standard_id')
            ->leftJoin('placement_round_slot as sl', fn ($j) => $j->on('sl.application_id', '=', 'rr.application_id')->where('sl.round_id', $round->id)->whereNull('sl.deleted_at'))
            ->where('rr.sub_institute_id', session('sub_institute_id'))->where('rr.round_id', $round->id)
            ->when($scope !== null, fn ($q) => $q->whereIn('e.standard_id', $scope ?: [0]))
            ->orderBy('s.first_name')
            ->selectRaw("rr.application_id, s.enrollment_no, CONCAT_WS(' ', s.first_name, s.last_name) as student, st.name as course,
                rr.attendance, rr.result, rr.score, rr.remarks, rr.published_at, sl.slot_start, sl.slot_end, sl.panel")
            ->get();

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['round'] = $round;
        $res['rows'] = $rows;
        $res['qr_url'] = route('placement.student.attend', [$round->id, $this->rounds->attendanceToken($round)]);
        $res['attendance'] = PlacementRoundResult::ATTENDANCE;
        $res['results'] = PlacementRoundResult::RESULTS;
        $res['summary'] = [
            'participants' => $rows->count(),
            'present'      => $rows->where('attendance', 'present')->count(),
            'cleared'      => $rows->where('result', 'cleared')->count(),
        ];

        return is_mobile($request->input('type'), 'placement/rounds/show', $res, 'view');
    }

    public function seed($round)
    {
        $round = PlacementDriveRound::findOrFail($round);
        $added = $this->rounds->seed($round);

        return $this->back($round, "{$added} participant(s) added.");
    }

    /** Saves attendance and/or result columns. Coordinators may only touch attendance for their courses. */
    public function save(PlacementRoundResultsRequest $request, $round)
    {
        $round = PlacementDriveRound::findOrFail($round);
        $rows = $request->input('rows');
        $scope = $this->students->scopeStandardIds();

        if ($scope !== null) {
            $allowed = $this->students->students(
                PlacementRoundResult::where('round_id', $round->id)->whereIn('application_id', array_keys($rows))->pluck('student_id')->all(), $scope
            )->keys();
            $rows = collect($rows)->filter(fn ($r, $appId) => PlacementRoundResult::where(['round_id' => $round->id, 'application_id' => $appId])
                ->whereIn('student_id', $allowed)->exists())->all();
        }

        $attendance = collect($rows)->filter(fn ($r) => ! empty($r['attendance']))->map(fn ($r) => $r['attendance'])->all();
        $changed = $this->rounds->markAttendance($round, $attendance);

        if ($scope === null) { // results are TPO-only
            $results = collect($rows)->filter(fn ($r) => ! empty($r['result']))->all();
            $changed += $this->rounds->recordResults($round, $results);
        }

        return $this->back($round, "{$changed} change(s) saved.");
    }

    public function publish($round)
    {
        $round = $this->rounds->publish(PlacementDriveRound::findOrFail($round));

        return $this->back($round, 'Results published and students notified.');
    }

    public function notifySchedule($round)
    {
        $round = PlacementDriveRound::findOrFail($round);
        $n = $this->rounds->notifySchedule($round);

        return $this->back($round, "{$n} notification(s) queued.");
    }

    public function storeSlot(PlacementSlotRequest $request, $round)
    {
        $round = PlacementDriveRound::findOrFail($round);
        $this->slots->schedule($round, (int) $request->application_id, Carbon::parse($request->slot_start), Carbon::parse($request->slot_end), $request->panel, $request->venue);

        return $this->back($round, 'Slot saved.');
    }

    public function autoSlots(Request $request, $round)
    {
        $data = $request->validate([
            'first_start' => 'required|date', 'minutes' => 'required|integer|min:5|max:240', 'panels' => 'required|string|max:255',
        ]);
        $round = PlacementDriveRound::findOrFail($round);
        $n = $this->slots->autoSchedule($round, Carbon::parse($data['first_start']), (int) $data['minutes'], array_map('trim', explode(',', $data['panels'])));

        return $this->back($round, "{$n} slot(s) scheduled.");
    }

    public function destroySlot($round, $slot)
    {
        $round = PlacementDriveRound::findOrFail($round);
        PlacementRoundSlot::where('round_id', $round->id)->findOrFail($slot)->delete();

        return $this->back($round, 'Slot removed.');
    }

    public function destroy($round)
    {
        $round = PlacementDriveRound::findOrFail($round);
        if ($round->isPublished() || PlacementRoundResult::where('round_id', $round->id)->exists()) {
            return redirect()->route('placement.rounds.index', $round->drive_id)
                ->with('data', ['status_code' => 0, 'message' => 'A round with participants or results cannot be deleted.']);
        }
        $round->delete();

        return redirect()->route('placement.rounds.index', $round->drive_id)->with('data', ['status_code' => 1, 'message' => 'Round deleted.']);
    }

    protected function back(PlacementDriveRound $round, string $message)
    {
        return redirect()->route('placement.rounds.show', $round->id)->with('data', ['status_code' => 1, 'message' => $message]);
    }
}
