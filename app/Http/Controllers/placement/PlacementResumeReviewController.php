<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Models\placement\PlacementResumeReview;
use App\Models\placement\PlacementStudentProfile;
use App\Services\Placement\AuditService;
use App\Services\Placement\NotificationService;
use App\Services\Placement\StudentLookup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use function App\Helpers\is_mobile;

/** Section 3H: resume review requests (student asks, TPO / coordinator reviews). */
class PlacementResumeReviewController extends Controller
{
    protected StudentLookup $students;

    public function __construct(StudentLookup $students)
    {
        $this->students = $students;
    }

    public function index(Request $request)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['counts'] = PlacementResumeReview::select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');

        return is_mobile($request->input('type'), 'placement/resume_review/index', $res, 'view');
    }

    public function data(Request $request)
    {
        $scope = $this->students->scopeStandardIds();

        $query = DB::table('placement_resume_review as r')
            ->join('tblstudent as s', 's.id', '=', 'r.student_id')
            ->leftJoin('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', session('syear')))
            ->leftJoin('standard as st', 'st.id', '=', 'e.standard_id')
            ->where('r.sub_institute_id', session('sub_institute_id'))->whereNull('r.deleted_at')
            ->when($request->filled('status'), fn ($q) => $q->where('r.status', $request->status))
            ->when($scope !== null, fn ($q) => $q->whereIn('e.standard_id', $scope ?: [0]))
            ->selectRaw("r.id, s.enrollment_no, CONCAT_WS(' ', s.first_name, s.last_name) as student, st.name as course, r.status, r.requested_at");

        return DataTables::query($query)
            ->addIndexColumn()
            ->editColumn('status', fn ($r) => '<span class="label label-'.($r->status === 'reviewed' ? 'success' : 'warning').'">'.ucfirst($r->status).'</span>')
            ->addColumn('action', fn ($r) => '<a class="btn btn-xs btn-info" href="'.route('placement.resume_reviews.show', $r->id).'">Open</a>')
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function show(Request $request, $review)
    {
        $review = PlacementResumeReview::findOrFail($review);
        $student = $this->students->students([$review->student_id], $this->students->scopeStandardIds())->first();
        abort_unless($student, 404);
        $profile = PlacementStudentProfile::with('skills')->where('student_id', $review->student_id)->first();

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['review'] = $review;
        $res['student'] = $student;
        $res['profile'] = $profile;
        $res['resume_url'] = $profile && $profile->resume_file ? Storage::disk(config('placement.disk'))->url($profile->resume_file) : null;

        return is_mobile($request->input('type'), 'placement/resume_review/show', $res, 'view');
    }

    public function respond(Request $request, AuditService $audit, NotificationService $notifier, $review)
    {
        $data = $request->validate(['feedback' => 'required|string|min:10|max:4000']);
        $review = PlacementResumeReview::findOrFail($review);
        abort_unless($this->students->students([$review->student_id], $this->students->scopeStandardIds())->isNotEmpty(), 404);

        $review->update(['feedback' => $data['feedback'], 'status' => 'reviewed', 'reviewer_id' => session('user_id'), 'reviewed_at' => now()]);
        $audit->log('resume_review', $review->id, 'review');
        $notifier->notify('announcement', [$review->student_id], ['title' => 'Resume review', 'notice' => 'Your resume review is ready. Open My Placement Profile to read the feedback.', 'date' => now()->format('d-m-Y')], 'resume_review', $review->id);

        return redirect()->route('placement.resume_reviews.index')->with('data', ['status_code' => 1, 'message' => 'Feedback sent to the student.']);
    }
}
