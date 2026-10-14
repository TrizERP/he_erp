<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Models\placement\PlacementTraining;
use App\Models\placement\PlacementTrainingAttendance;
use App\Services\Placement\AuditService;
use App\Services\Placement\StudentLookup;
use App\Services\Placement\TrainingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use function App\Helpers\is_mobile;

/** Section 3H: pre-placement training, mock tests and mock interviews. */
class PlacementTrainingController extends Controller
{
    protected TrainingService $training;
    protected StudentLookup $students;

    public function __construct(TrainingService $training, StudentLookup $students)
    {
        $this->training = $training;
        $this->students = $students;
    }

    public function index(Request $request)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['types'] = PlacementTraining::TYPES;

        return is_mobile($request->input('type'), 'placement/training/index', $res, 'view');
    }

    public function data(Request $request)
    {
        $query = DB::table('placement_training as t')
            ->where('t.sub_institute_id', session('sub_institute_id'))->whereNull('t.deleted_at')
            ->when($request->filled('training_type'), fn ($q) => $q->where('t.training_type', $request->training_type))
            ->selectRaw("t.id, t.title, t.training_type, t.trainer, t.session_date, t.status,
                (select count(*) from placement_training_attendance a where a.training_id = t.id) as participants,
                (select count(*) from placement_training_attendance a where a.training_id = t.id and a.attendance = 'present') as present");

        return DataTables::query($query)
            ->addIndexColumn()
            ->editColumn('training_type', fn ($r) => PlacementTraining::TYPES[$r->training_type] ?? $r->training_type)
            ->editColumn('status', fn ($r) => ucfirst($r->status))
            ->addColumn('action', fn ($r) => '<a class="btn btn-xs btn-info" href="'.route('placement.training.show', $r->id).'"><i class="fa fa-eye"></i></a>')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create(Request $request)
    {
        return $this->form($request, new PlacementTraining());
    }

    public function store(Request $request, AuditService $audit)
    {
        $data = $this->validated($request);
        $training = DB::transaction(function () use ($data, $audit) {
            $t = PlacementTraining::create($data);
            $audit->log('training', $t->id, 'create', null, $data);
            $this->training->assign($t);

            return $t;
        });

        return redirect()->route('placement.training.show', $training->id)
            ->with('data', ['status_code' => 1, 'message' => 'Session created and students assigned.']);
    }

    public function show(Request $request, $training)
    {
        $training = PlacementTraining::findOrFail($training);
        $scope = $this->students->scopeStandardIds();

        $rows = DB::table('placement_training_attendance as ta')
            ->join('tblstudent as s', 's.id', '=', 'ta.student_id')
            ->leftJoin('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', session('syear')))
            ->leftJoin('standard as st', 'st.id', '=', 'e.standard_id')
            ->where('ta.sub_institute_id', session('sub_institute_id'))->where('ta.training_id', $training->id)
            ->when($scope !== null, fn ($q) => $q->whereIn('e.standard_id', $scope ?: [0]))
            ->orderBy('s.first_name')
            ->selectRaw("ta.student_id, s.enrollment_no, CONCAT_WS(' ', s.first_name, s.last_name) as student, st.name as course,
                ta.attendance, ta.score, ta.rating, ta.feedback")
            ->get();

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['training'] = $training;
        $res['rows'] = $rows;
        $res['types'] = PlacementTraining::TYPES;
        $res['standards'] = $this->standardNames($training->standard_ids);

        return is_mobile($request->input('type'), 'placement/training/show', $res, 'view');
    }

    public function edit(Request $request, $training)
    {
        return $this->form($request, PlacementTraining::findOrFail($training));
    }

    public function update(Request $request, AuditService $audit, $training)
    {
        $training = PlacementTraining::findOrFail($training);
        $training->fill($this->validated($request));
        [$old, $new] = $audit->diff($training->getOriginal(), $training->getDirty());
        $training->save();
        $this->training->assign($training);
        if ($new) {
            $audit->log('training', $training->id, 'update', $old, $new);
        }

        return redirect()->route('placement.training.show', $training->id)->with('data', ['status_code' => 1, 'message' => 'Session updated.']);
    }

    public function destroy(AuditService $audit, $training)
    {
        $training = PlacementTraining::findOrFail($training);
        DB::transaction(function () use ($training, $audit) {
            $training->delete();
            $audit->log('training', $training->id, 'delete');
        });

        return redirect()->route('placement.training.index')->with('data', ['status_code' => 1, 'message' => 'Session deleted.']);
    }

    /** Re-adds newly registered students of the assigned courses. */
    public function assign($training)
    {
        $training = PlacementTraining::findOrFail($training);
        $n = $this->training->assign($training);

        return redirect()->route('placement.training.show', $training->id)->with('data', ['status_code' => 1, 'message' => "{$n} student(s) added."]);
    }

    /** Attendance, scores, ratings and feedback. Coordinators: own courses only. */
    public function save(Request $request, $training)
    {
        $data = $request->validate([
            'rows'              => 'required|array|max:1000',
            'rows.*.attendance' => ['nullable', Rule::in(['pending', 'present', 'absent'])],
            'rows.*.score'      => 'nullable|numeric|min:0|max:100000',
            'rows.*.rating'     => 'nullable|integer|min:1|max:5',
            'rows.*.feedback'   => 'nullable|string|max:2000',
        ]);
        $training = PlacementTraining::findOrFail($training);

        $scope = $this->students->scopeStandardIds();
        $allowed = $scope === null ? null : $this->students->students(array_keys($data['rows']), $scope)->keys()->map(fn ($v) => (int) $v)->all();
        $n = $this->training->record($training, $data['rows'], $allowed);

        return redirect()->route('placement.training.show', $training->id)->with('data', ['status_code' => 1, 'message' => "{$n} row(s) saved."]);
    }

    // ------------------------------------------------------------ helpers

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title'         => 'required|string|max:200',
            'training_type' => ['required', Rule::in(array_keys(PlacementTraining::TYPES))],
            'trainer'       => 'nullable|string|max:150',
            'session_date'  => 'required|date',
            'start_time'    => 'nullable|date_format:H:i',
            'end_time'      => 'nullable|date_format:H:i|after:start_time',
            'venue'         => 'nullable|string|max:255',
            'max_score'     => 'nullable|numeric|min:0|max:100000',
            'description'   => 'nullable|string|max:3000',
            'status'        => ['required', Rule::in(['scheduled', 'completed', 'cancelled'])],
            'standard_ids'  => 'nullable|array',
            'standard_ids.*' => ['integer', Rule::exists('standard', 'id')->where('sub_institute_id', session('sub_institute_id'))],
        ]);
        $data['standard_ids'] = $data['standard_ids'] ?? [];

        return $data;
    }

    protected function form(Request $request, PlacementTraining $training)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['training'] = $training;
        $res['types'] = PlacementTraining::TYPES;
        $res['standards'] = DB::table('standard')->where('sub_institute_id', session('sub_institute_id'))->orderBy('sort_order')->pluck('name', 'id');

        return is_mobile($request->input('type'), 'placement/training/form', $res, 'view');
    }

    protected function standardNames(?array $ids): string
    {
        if (! $ids) {
            return 'All courses';
        }

        return DB::table('standard')->whereIn('id', $ids)->pluck('name')->implode(', ');
    }
}
