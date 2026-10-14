<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Http\Requests\placement\PlacementVerificationRequest;
use App\Models\placement\PlacementStudentProfile;
use App\Services\Placement\ProfileService;
use App\Services\Placement\StudentLookup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use function App\Helpers\is_mobile;

/** Section 3E: TPO / coordinator verification of student placement profiles (Pending / Verified / Rejected). */
class PlacementVerificationController extends Controller
{
    protected StudentLookup $students;
    protected ProfileService $profiles;

    public function __construct(StudentLookup $students, ProfileService $profiles)
    {
        $this->students = $students;
        $this->profiles = $profiles;
    }

    public function index(Request $request)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['counts'] = PlacementStudentProfile::where('registration_status', 'registered')
            ->select('verification_status', DB::raw('COUNT(*) as total'))->groupBy('verification_status')->pluck('total', 'verification_status');

        return is_mobile($request->input('type'), 'placement/verification/index', $res, 'view');
    }

    public function data(Request $request)
    {
        $query = DB::table('placement_student_profile as p')
            ->join('tblstudent as s', 's.id', '=', 'p.student_id')
            ->join('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', session('syear')))
            ->leftJoin('standard as st', 'st.id', '=', 'e.standard_id')
            ->where('p.sub_institute_id', session('sub_institute_id'))
            ->whereNull('p.deleted_at')
            ->where('p.registration_status', 'registered')
            ->when($request->filled('status'), fn ($q) => $q->where('p.verification_status', $request->status))
            ->when(($scope = $this->students->scopeStandardIds()) !== null, fn ($q) => $q->whereIn('e.standard_id', $scope ?: [0]))
            ->selectRaw("p.id, s.enrollment_no, CONCAT_WS(' ', s.first_name, s.last_name) as student, st.name as course,
                p.cgpa, p.active_backlogs, p.completeness, p.verification_status, p.updated_at");

        return DataTables::query($query)
            ->addIndexColumn()
            ->editColumn('completeness', fn ($r) => $r->completeness.'%')
            ->editColumn('verification_status', fn ($r) => '<span class="label label-'.['pending' => 'warning', 'verified' => 'success', 'rejected' => 'danger'][$r->verification_status].'">'.ucfirst($r->verification_status).'</span>')
            ->addColumn('action', fn ($r) => '<a class="btn btn-xs btn-info" href="'.route('placement.verification.show', $r->id).'"><i class="fa fa-eye"></i> Review</a>')
            ->rawColumns(['verification_status', 'action'])
            ->toJson();
    }

    public function show(Request $request, $profile)
    {
        $profile = PlacementStudentProfile::with('skills')->findOrFail($profile);
        $student = $this->scopedStudent($profile);

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['profile'] = $profile;
        $res['student'] = $student;
        $res['resume_url'] = $profile->resume_file ? Storage::disk(config('placement.disk'))->url($profile->resume_file) : null;

        return is_mobile($request->input('type'), 'placement/verification/show', $res, 'view');
    }

    public function decide(PlacementVerificationRequest $request, $profile)
    {
        $profile = PlacementStudentProfile::findOrFail($profile);
        $this->scopedStudent($profile);
        $this->profiles->decide($profile, $request->status, $request->remarks);

        return redirect()->route('placement.verification.index')
            ->with('data', ['status_code' => 1, 'message' => 'Profile '.$request->status.'.']);
    }

    /** 404s when a coordinator opens a student outside their courses. */
    protected function scopedStudent(PlacementStudentProfile $profile)
    {
        $student = $this->students->students([$profile->student_id], $this->students->scopeStandardIds())->first();
        abort_unless($student, 404);

        return $student;
    }
}
