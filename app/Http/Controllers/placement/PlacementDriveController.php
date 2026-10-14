<?php

namespace App\Http\Controllers\placement;

use App\Exports\Placement\ApplicantsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\placement\PlacementApplicationStatusRequest;
use App\Http\Requests\placement\PlacementDriveEligibilityRequest;
use App\Http\Requests\placement\PlacementDriveRequest;
use App\Http\Requests\placement\PlacementDriveRoleRequest;
use App\Http\Requests\placement\PlacementDriveStatusRequest;
use App\Http\Requests\placement\PlacementOverrideApplyRequest;
use App\Models\placement\PlacementApplication;
use App\Models\placement\PlacementCompany;
use App\Models\placement\PlacementDrive;
use App\Models\placement\PlacementDriveEligibility;
use App\Models\placement\PlacementDriveRole;
use App\Models\placement\PlacementEligibilityTemplate;
use App\Models\placement\PlacementMaster;
use App\Models\placement\PlacementSeason;
use App\Models\placement\PlacementStudentProfile;
use App\Services\Placement\ApplicationService;
use App\Services\Placement\AuditService;
use App\Services\Placement\DriveService;
use App\Services\Placement\EligibilityService;
use App\Services\Placement\StudentLookup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use function App\Helpers\is_mobile;

/**
 * Section 3D: drives, roles, per-role eligibility, eligible list, applicants and shortlisting.
 * Coordinators see every drive but only their own courses' students.
 */
class PlacementDriveController extends Controller
{
    protected AuditService $audit;
    protected DriveService $drives;
    protected StudentLookup $students;

    public function __construct(AuditService $audit, DriveService $drives, StudentLookup $students)
    {
        $this->audit = $audit;
        $this->drives = $drives;
        $this->students = $students;
    }

    public function index(Request $request)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['statuses'] = PlacementDrive::STATUSES;
        $res['status_counts'] = PlacementDrive::select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');

        return is_mobile($request->input('type'), 'placement/drive/index', $res, 'view');
    }

    public function data(Request $request)
    {
        $query = PlacementDrive::query()
            ->join('placement_company as c', 'c.id', '=', 'placement_drive.company_id')
            ->select('placement_drive.*', 'c.name as company_name',
                DB::raw('(select count(*) from placement_application a where a.drive_id = placement_drive.id and a.status <> "withdrawn" and a.deleted_at is null) as applicants'))
            ->when($request->filled('status'), fn ($q) => $q->where('placement_drive.status', $request->status));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filterColumn('company_name', fn ($q, $kw) => $q->where('c.name', 'like', "%{$kw}%"))
            ->editColumn('drive_mode', fn ($r) => PlacementDrive::MODES[$r->drive_mode] ?? $r->drive_mode)
            ->editColumn('drive_date', fn ($r) => $r->drive_date?->format('d-m-Y'))
            ->editColumn('registration_deadline', fn ($r) => $r->registration_deadline?->format('d-m-Y H:i'))
            ->editColumn('status', fn ($r) => '<span class="label label-'.$this->badge($r->status).'">'.(PlacementDrive::STATUSES[$r->status] ?? $r->status).'</span>')
            ->addColumn('action', fn ($r) => '<a class="btn btn-xs btn-info" href="'.route('placement.drives.show', $r->id).'"><i class="fa fa-eye"></i></a>')
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(Request $request)
    {
        return $this->form($request, new PlacementDrive());
    }

    public function store(PlacementDriveRequest $request)
    {
        $drive = DB::transaction(function () use ($request) {
            $drive = PlacementDrive::create($request->safe()->except('jd_file'));
            $this->saveJd($request, $drive);
            $this->audit->log('drive', $drive->id, 'create', null, $request->safe()->except('jd_file'));

            return $drive;
        });

        return $this->back($drive, 'Drive created. Add roles and eligibility, then publish.');
    }

    public function show(Request $request, $drive)
    {
        $drive = PlacementDrive::with(['company', 'roles', 'eligibilities'])->findOrFail($drive);

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['drive'] = $drive;
        $res['next_statuses'] = $this->drives->allowedNext($drive);
        $res['statuses'] = PlacementDrive::STATUSES;
        $res['modes'] = PlacementDrive::MODES;
        $res['jd_url'] = $drive->jd_file ? Storage::disk(config('placement.disk'))->url($drive->jd_file) : null;
        $res['job_types'] = PlacementMaster::where('master_type', 'job_type')->where('status', 1)->orderBy('sort_order')->pluck('name', 'id');
        $res['job_roles'] = PlacementMaster::where('master_type', 'job_role')->where('status', 1)->orderBy('sort_order')->pluck('name', 'id');
        $res['templates'] = PlacementEligibilityTemplate::where('status', 1)->orderBy('name')->get();
        $res['standards'] = DB::table('standard')->where('sub_institute_id', session('sub_institute_id'))->orderBy('sort_order')->pluck('name', 'id');
        $res['counts'] = [
            'eligible'     => DB::table('placement_drive_eligible_student')->where('sub_institute_id', session('sub_institute_id'))->where(['drive_id' => $drive->id, 'is_eligible' => 1])->distinct()->count('student_id'),
            'applications' => PlacementApplication::where('drive_id', $drive->id)->whereIn('status', PlacementApplication::ACTIVE)->count(),
            'shortlisted'  => PlacementApplication::where('drive_id', $drive->id)->where('status', 'shortlisted')->count(),
        ];
        $res['editable'] = in_array($drive->status, ['draft', 'published'], true);

        return is_mobile($request->input('type'), 'placement/drive/show', $res, 'view');
    }

    public function edit(Request $request, $drive)
    {
        return $this->form($request, PlacementDrive::findOrFail($drive));
    }

    public function update(PlacementDriveRequest $request, $drive)
    {
        $drive = PlacementDrive::findOrFail($drive);
        if (in_array($drive->status, ['completed', 'cancelled'], true)) {
            return $this->back($drive, 'A '.$drive->status.' drive cannot be edited.', 0);
        }

        DB::transaction(function () use ($request, $drive) {
            $drive->fill($request->safe()->except('jd_file'));
            [$old, $new] = $this->audit->diff($drive->getOriginal(), $drive->getDirty());
            $drive->save();
            $this->saveJd($request, $drive);
            if ($new) {
                $this->audit->log('drive', $drive->id, 'update', $old, $new);
            }
        });

        return $this->back($drive, 'Drive updated.');
    }

    public function destroy(Request $request, $drive)
    {
        $drive = PlacementDrive::findOrFail($drive);
        if ($drive->status !== 'draft') {
            return $this->back($drive, 'Only draft drives can be deleted; cancel it instead.', 0);
        }

        DB::transaction(function () use ($drive) {
            $drive->delete();
            $this->audit->log('drive', $drive->id, 'delete');
        });

        return redirect()->route('placement.drives.index')->with('data', ['status_code' => 1, 'message' => 'Drive deleted.']);
    }

    public function transition(PlacementDriveStatusRequest $request, $drive)
    {
        $drive = PlacementDrive::findOrFail($drive);
        $this->drives->transition($drive, $request->status, $request->reason);

        return $this->back($drive, 'Drive moved to '.PlacementDrive::STATUSES[$request->status].'.');
    }

    // ------------------------------------------------------------ roles

    public function storeRole(PlacementDriveRoleRequest $request, $drive)
    {
        $drive = $this->editableDrive($drive);
        $role = $drive->roles()->create($request->validated());
        $this->audit->log('drive_role', $role->id, 'create', null, $request->validated());

        return $this->back($drive, 'Role added.');
    }

    public function destroyRole($drive, $role)
    {
        $drive = $this->editableDrive($drive);
        $role = $drive->roles()->findOrFail($role);
        if (PlacementApplication::where('role_id', $role->id)->exists()) {
            return $this->back($drive, 'This role already has applications and cannot be removed.', 0);
        }

        DB::transaction(function () use ($drive, $role) {
            PlacementDriveEligibility::where(['drive_id' => $drive->id, 'role_id' => $role->id])->delete();
            $role->delete();
            $this->audit->log('drive_role', $role->id, 'delete');
        });

        return $this->back($drive, 'Role removed.');
    }

    // ------------------------------------------------------------ eligibility

    public function saveEligibility(PlacementDriveEligibilityRequest $request, $drive)
    {
        $drive = $this->editableDrive($drive);
        $data = $request->validated();

        // Picking a template pre-fills any criterion the TPO left blank (the TPO can still tune it per drive).
        if (! empty($data['template_id'])) {
            $t = PlacementEligibilityTemplate::find($data['template_id']);
            foreach (['min_cgpa', 'min_10th_percent', 'min_12th_percent', 'max_active_backlogs', 'passing_year', 'gender'] as $k) {
                $data[$k] = $data[$k] ?? $t->{$k};
            }
            $data['standard_ids'] = $data['standard_ids'] ?? $t->standard_ids;
        }

        DB::transaction(function () use ($drive, $data) {
            $row = PlacementDriveEligibility::firstOrNew(['drive_id' => $drive->id, 'role_id' => $data['role_id'] ?? null]);
            $row->fill($data)->save();
            $this->audit->log('drive_eligibility', $row->id, 'save', null, $data);
        });

        return $this->back($drive, 'Eligibility criteria saved. Recompute the eligible list to apply them.');
    }

    public function destroyEligibility($drive, $eligibility)
    {
        $drive = $this->editableDrive($drive);
        PlacementDriveEligibility::where('drive_id', $drive->id)->findOrFail($eligibility)->delete();

        return $this->back($drive, 'Criteria removed.');
    }

    public function recompute(EligibilityService $service, $drive)
    {
        $drive = PlacementDrive::findOrFail($drive);
        $summary = $service->computeForDrive($drive);
        $eligible = collect($summary)->sum('eligible');
        $this->audit->log('drive', $drive->id, 'recompute_eligibility', null, $summary);

        return $this->back($drive, "Eligibility recomputed ({$eligible} eligible role-slots).");
    }

    public function eligibleData(Request $request, $drive)
    {
        $drive = PlacementDrive::findOrFail($drive);

        $query = DB::table('placement_drive_eligible_student as pe')
            ->join('tblstudent as s', 's.id', '=', 'pe.student_id')
            ->join('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', session('syear')))
            ->join('placement_drive_role as r', 'r.id', '=', 'pe.role_id')
            ->leftJoin('standard as st', 'st.id', '=', 'e.standard_id')
            ->where('pe.sub_institute_id', session('sub_institute_id'))
            ->where('pe.drive_id', $drive->id)
            ->when($request->filled('eligible'), fn ($q) => $q->where('pe.is_eligible', (int) $request->eligible))
            ->when(($scope = $this->students->scopeStandardIds()) !== null, fn ($q) => $q->whereIn('e.standard_id', $scope ?: [0]))
            ->selectRaw("pe.id, s.enrollment_no, CONCAT_WS(' ', s.first_name, s.last_name) as student, st.name as course, r.title as role, pe.is_eligible, pe.reasons");

        return DataTables::query($query)
            ->addIndexColumn()
            ->editColumn('is_eligible', fn ($r) => $r->is_eligible ? '<span class="label label-success">Eligible</span>' : '<span class="label label-danger">Not eligible</span>')
            ->editColumn('reasons', fn ($r) => $r->reasons ? e(implode('; ', json_decode($r->reasons, true))) : '')
            ->rawColumns(['is_eligible'])
            ->toJson();
    }

    // ------------------------------------------------------------ applicants & shortlisting

    public function applicantsData(Request $request, $drive)
    {
        return DataTables::query($this->applicantQuery($request, (int) $drive))
            ->addIndexColumn()
            ->addColumn('checkbox', fn ($r) => '<input type="checkbox" class="app-check" value="'.$r->id.'">')
            ->editColumn('status', fn ($r) => '<span class="label label-'.($r->status === 'shortlisted' ? 'success' : ($r->status === 'rejected' ? 'danger' : 'default')).'">'.(PlacementApplication::STATUSES[$r->status] ?? $r->status).'</span>'
                .($r->override_reason ? ' <span class="label label-warning" title="'.e($r->override_reason).'">override</span>' : ''))
            ->rawColumns(['checkbox', 'status'])
            ->toJson();
    }

    public function applicantsExport(Request $request, $drive)
    {
        $drive = PlacementDrive::findOrFail($drive);
        $rows = $this->applicantQuery($request, $drive->id)->get()->map(fn ($r) => [
            $r->enrollment_no, $r->student, $r->course, $r->role, $r->cgpa, $r->active_backlogs,
            PlacementApplication::STATUSES[$r->status] ?? $r->status, $r->applied_at, $r->override_reason,
        ]);

        return Excel::download(new ApplicantsExport($rows), 'applicants_drive_'.$drive->id.'.xlsx');
    }

    public function bulkStatus(PlacementApplicationStatusRequest $request, ApplicationService $service, $drive)
    {
        $drive = PlacementDrive::findOrFail($drive);
        $result = $service->setStatus(
            $request->application_ids, $request->status, $request->remarks, $request->override_reason,
            $this->students->scopeStandardIds()
        );

        $message = "{$result['changed']} application(s) updated.";
        if ($result['blocked']) {
            $lines = collect($result['blocked'])->map(fn ($v, $id) => "#{$id}: ".implode(' ', $v))->take(8)->implode(' | ');
            $message .= ' '.count($result['blocked'])." blocked - {$lines}. Provide an override reason to force a policy block.";
        }

        return $this->back($drive, $message, $result['blocked'] && ! $result['changed'] ? 0 : 1);
    }

    /** TPO adds a student despite a policy/eligibility block; the reason is mandatory and audited. */
    public function overrideApply(PlacementOverrideApplyRequest $request, ApplicationService $service, $drive)
    {
        $drive = PlacementDrive::findOrFail($drive);
        $role = $drive->roles()->findOrFail($request->role_id);
        $studentId = DB::table('tblstudent')->where('sub_institute_id', session('sub_institute_id'))
            ->where('enrollment_no', $request->enrollment_no)->value('id');
        $profile = PlacementStudentProfile::where('student_id', $studentId)->first();

        if (! $profile) {
            return $this->back($drive, 'That student has no placement profile yet.', 0);
        }

        $service->apply($profile, $drive, $role, $request->override_reason);

        return $this->back($drive, 'Application recorded with override.');
    }

    // ------------------------------------------------------------ helpers

    protected function applicantQuery(Request $request, int $driveId)
    {
        return DB::table('placement_application as a')
            ->join('tblstudent as s', 's.id', '=', 'a.student_id')
            ->join('placement_drive_role as r', 'r.id', '=', 'a.role_id')
            ->leftJoin('placement_student_profile as p', fn ($j) => $j->on('p.student_id', '=', 'a.student_id')->on('p.sub_institute_id', '=', 'a.sub_institute_id'))
            ->leftJoin('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', session('syear')))
            ->leftJoin('standard as st', 'st.id', '=', 'e.standard_id')
            ->where('a.sub_institute_id', session('sub_institute_id'))
            ->where('a.drive_id', $driveId)
            ->whereNull('a.deleted_at')
            ->when($request->filled('status'), fn ($q) => $q->where('a.status', $request->status))
            ->when(($scope = $this->students->scopeStandardIds()) !== null, fn ($q) => $q->whereIn('e.standard_id', $scope ?: [0]))
            ->selectRaw("a.id, s.enrollment_no, CONCAT_WS(' ', s.first_name, s.last_name) as student, st.name as course,
                r.title as role, p.cgpa, p.active_backlogs, a.status, a.applied_at, a.override_reason");
    }

    protected function editableDrive($id): PlacementDrive
    {
        $drive = PlacementDrive::findOrFail($id);
        abort_unless(in_array($drive->status, ['draft', 'published'], true), 422, 'Roles and criteria can only change while the drive is Draft or Published.');

        return $drive;
    }

    protected function form(Request $request, PlacementDrive $drive)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['drive'] = $drive;
        $res['companies'] = PlacementCompany::where('status', '!=', 'blacklisted')->orderBy('name')->pluck('name', 'id');
        $res['seasons'] = PlacementSeason::orderByDesc('id')->pluck('name', 'id');
        $res['modes'] = PlacementDrive::MODES;

        return is_mobile($request->input('type'), 'placement/drive/form', $res, 'view');
    }

    protected function saveJd(Request $request, PlacementDrive $drive): void
    {
        if (! $request->hasFile('jd_file')) {
            return;
        }
        $disk = config('placement.disk');
        $path = $request->file('jd_file')->store('placement/'.$drive->sub_institute_id.'/jd', $disk);
        if ($drive->jd_file) {
            Storage::disk($disk)->delete($drive->jd_file);
        }
        $drive->update(['jd_file' => $path]);
    }

    protected function back(PlacementDrive $drive, string $message, int $ok = 1)
    {
        return redirect()->route('placement.drives.show', $drive->id)->with('data', ['status_code' => $ok, 'message' => $message]);
    }

    protected function badge(string $status): string
    {
        return ['draft' => 'default', 'published' => 'info', 'registration_open' => 'success', 'registration_closed' => 'warning',
            'in_progress' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'][$status] ?? 'default';
    }
}
