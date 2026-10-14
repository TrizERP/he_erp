<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Http\Requests\placement\PlacementStudentProfileRequest;
use App\Models\placement\PlacementAnnouncement;
use App\Models\placement\PlacementApplication;
use App\Models\placement\PlacementDrive;
use App\Models\placement\PlacementDriveRole;
use App\Models\placement\PlacementDriveRound;
use App\Models\placement\PlacementMaster;
use App\Models\placement\PlacementNotificationLog;
use App\Models\placement\PlacementOffer;
use App\Models\placement\PlacementResumeReview;
use App\Models\placement\PlacementRoundResult;
use App\Models\placement\PlacementRoundSlot;
use App\Models\placement\PlacementStudentProfile;
use App\Models\placement\PlacementTraining;
use App\Services\Placement\ApplicationService;
use App\Services\Placement\AuditService;
use App\Services\Placement\OfferService;
use App\Services\Placement\PlacementAccess;
use App\Services\Placement\ProfileService;
use App\Services\Placement\RoundService;
use App\Services\Placement\StudentLookup;
use App\Services\Placement\TrainingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use function App\Helpers\is_mobile;

/**
 * Student-facing placement screens (profile, registration, eligible drives, applications, resume).
 * Every query is keyed on the logged-in student's own id; nothing here accepts a student id from the request.
 */
class StudentPlacementController extends Controller
{
    protected ProfileService $profiles;
    protected StudentLookup $students;

    public function __construct(ProfileService $profiles, StudentLookup $students)
    {
        $this->profiles = $profiles;
        $this->students = $students;
    }

    // ------------------------------------------------------------ profile

    public function profile(Request $request)
    {
        $profile = $this->profiles->forStudent($this->studentId());
        $profile->load('skills');

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['profile'] = $profile;
        $res['student'] = $this->students->find($profile->student_id);
        $res['skill_options'] = PlacementMaster::where('master_type', 'skill')->where('status', 1)->orderBy('name')->pluck('name');
        $res['job_types'] = PlacementMaster::where('master_type', 'job_type')->where('status', 1)->orderBy('sort_order')->pluck('name');
        $res['resume_url'] = $profile->resume_file ? Storage::disk(config('placement.disk'))->url($profile->resume_file) : null;

        return is_mobile($request->input('type'), 'placement/student/profile', $res, 'view');
    }

    public function updateProfile(PlacementStudentProfileRequest $request)
    {
        $profile = $this->profiles->forStudent($this->studentId());
        $wasVerified = $profile->verification_status === 'verified';

        DB::transaction(function () use ($request, $profile, $wasVerified) {
            $profile->fill($request->safe()->only(['cgpa', 'tenth_percent', 'twelfth_percent', 'active_backlogs', 'passing_year',
                'linkedin_url', 'github_url', 'portfolio_url']));

            $profile->projects = $this->cleanRows($request->input('projects', []), 'title');
            $profile->certifications = $this->cleanRows($request->input('certifications', []), 'title');
            $profile->achievements = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $request->input('achievements', '')))));
            $profile->preferences = array_filter([
                'locations' => array_values(array_filter(array_map('trim', explode(',', (string) $request->input('preferred_locations', ''))))),
                'job_types' => $request->input('preferred_job_types', []),
            ]);

            if ($request->hasFile('resume')) {
                $disk = config('placement.disk');
                $path = $request->file('resume')->store('placement/'.$profile->sub_institute_id.'/resumes', $disk);
                if ($profile->resume_file) {
                    Storage::disk($disk)->delete($profile->resume_file);
                }
                $profile->resume_file = $path;
            }

            // Changing a verified academic fact sends the profile back for re-verification.
            $reverify = $wasVerified && $this->profiles->touchesVerifiedField($profile);
            if ($reverify) {
                $profile->verification_status = 'pending';
                $profile->verification_remarks = 'Academic details changed by student; re-verification required.';
            }
            $profile->save();

            $profile->skills()->delete();
            foreach ($request->input('skills', []) as $skill) {
                if (! empty($skill['name'])) {
                    $profile->skills()->create(['student_id' => $profile->student_id, 'skill_name' => $skill['name'], 'level' => $skill['level'] ?: 'intermediate']);
                }
            }

            $this->profiles->refreshCompleteness($profile);
            app(AuditService::class)->log('student_profile', $profile->id, $reverify ? 'update_reverify' : 'update');
        });

        return redirect()->route('placement.student.profile')
            ->with('data', ['status_code' => 1, 'message' => 'Profile saved ('.$profile->completeness.'% complete).']);
    }

    public function register(Request $request)
    {
        $request->validate(['undertaking' => 'accepted'], ['undertaking.accepted' => 'You must accept the placement undertaking.']);
        $this->profiles->register($this->profiles->forStudent($this->studentId()));

        return redirect()->route('placement.student.profile')
            ->with('data', ['status_code' => 1, 'message' => 'Registered for placements. Your profile now awaits verification.']);
    }

    public function optOut(Request $request)
    {
        $this->profiles->optOut($this->profiles->forStudent($this->studentId()));

        return redirect()->route('placement.student.profile')
            ->with('data', ['status_code' => 1, 'message' => 'You have opted out of placements.']);
    }

    public function resume()
    {
        $profile = $this->profiles->forStudent($this->studentId());
        $profile->load('skills');

        return Pdf::loadView('placement.student.resume_pdf', [
            'profile' => $profile, 'student' => $this->students->find($profile->student_id),
        ])->download('resume_'.$profile->student_id.'.pdf');
    }

    // ------------------------------------------------------------ drives & applications

    /** Only drives where the student is eligible for at least one role are ever listed. */
    public function drives(Request $request)
    {
        $profile = $this->profiles->forStudent($this->studentId());

        $rows = DB::table('placement_drive_eligible_student as pe')
            ->where('pe.sub_institute_id', session('sub_institute_id'))
            ->where(['pe.student_id' => $profile->student_id, 'pe.is_eligible' => 1])
            ->get(['pe.drive_id', 'pe.role_id']);

        $drives = PlacementDrive::with(['company:id,name,website', 'roles'])
            ->whereIn('id', $rows->pluck('drive_id')->unique())
            ->whereIn('status', ['published', 'registration_open', 'registration_closed', 'in_progress'])
            ->orderBy('drive_date')->get();

        $eligibleRoles = $rows->groupBy('drive_id')->map(fn ($g) => $g->pluck('role_id')->all());
        $applied = PlacementApplication::where('student_id', $profile->student_id)->get()->keyBy(fn ($a) => $a->drive_id.'-'.$a->role_id);

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['profile'] = $profile;
        $res['drives'] = $drives->map(function (PlacementDrive $d) use ($eligibleRoles, $applied) {
            return [
                'drive'  => $d,
                'jd_url' => $d->jd_file ? Storage::disk(config('placement.disk'))->url($d->jd_file) : null,
                'open'   => $d->isRegistrationOpen(),
                'roles'  => $d->roles->whereIn('id', $eligibleRoles[$d->id] ?? [])->map(fn ($r) => [
                    'role' => $r, 'application' => $applied[$d->id.'-'.$r->id] ?? null,
                ])->values(),
            ];
        })->filter(fn ($d) => $d['roles']->isNotEmpty())->values();

        return is_mobile($request->input('type'), 'placement/student/drives', $res, 'view');
    }

    public function apply(Request $request, ApplicationService $service, $drive)
    {
        $request->validate(['role_id' => 'required|integer']);
        $profile = $this->profiles->forStudent($this->studentId());
        $drive = PlacementDrive::findOrFail($drive);
        $role = PlacementDriveRole::where('drive_id', $drive->id)->findOrFail($request->role_id);

        try {
            $service->apply($profile, $drive, $role);
        } catch (ValidationException $e) {
            // Show the policy reasons ("blocks ineligible or placed students and shows the reason").
            return redirect()->route('placement.student.drives')
                ->with('data', ['status_code' => 0, 'message' => implode(' ', collect($e->errors())->flatten()->all())]);
        }

        return redirect()->route('placement.student.applications')
            ->with('data', ['status_code' => 1, 'message' => 'Application submitted for '.$role->title.'.']);
    }

    public function applications(Request $request)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['applications'] = PlacementApplication::with(['drive.company:id,name', 'role'])
            ->where('student_id', $this->studentId())->orderByDesc('applied_at')->get();
        $res['statuses'] = PlacementApplication::STATUSES;

        return is_mobile($request->input('type'), 'placement/student/applications', $res, 'view');
    }

    public function withdraw(ApplicationService $service, $application)
    {
        $application = PlacementApplication::with('drive')->where('student_id', $this->studentId())->findOrFail($application);
        $service->withdraw($application);

        return redirect()->route('placement.student.applications')
            ->with('data', ['status_code' => 1, 'message' => 'Application withdrawn.']);
    }

    // ------------------------------------------------------------ rounds, offers, notices

    /** My rounds: schedule and slot always; the result only once the TPO has published it. */
    public function rounds(Request $request)
    {
        $studentId = $this->studentId();
        $rows = PlacementRoundResult::with(['round.drive.company'])->where('student_id', $studentId)->get();
        $slots = PlacementRoundSlot::where('student_id', $studentId)->get()->keyBy('application_id');

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['rounds'] = $rows->sortBy(fn ($r) => $r->round->round_date)->map(fn ($r) => [
            'round'      => $r->round,
            'attendance' => $r->attendance,
            'result'     => $r->published_at ? $r->result : null,
            'score'      => $r->published_at ? $r->score : null,
            'remarks'    => $r->published_at ? $r->remarks : null,
            'slot'       => $slots[$r->application_id] ?? null,
        ])->values();

        return is_mobile($request->input('type'), 'placement/student/rounds', $res, 'view');
    }

    /** Scanning the QR shown at the round marks the student present. */
    public function attend(RoundService $rounds, $round, $token)
    {
        try {
            $rounds->markAttendanceByQr(PlacementDriveRound::findOrFail($round), $this->studentId(), $token);
        } catch (ValidationException $e) {
            return redirect()->route('placement.student.rounds')
                ->with('data', ['status_code' => 0, 'message' => collect($e->errors())->flatten()->implode(' ')]);
        }

        return redirect()->route('placement.student.rounds')->with('data', ['status_code' => 1, 'message' => 'Attendance marked. Good luck!']);
    }

    public function offers(Request $request)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['offers'] = PlacementOffer::with('company:id,name')->where('student_id', $this->studentId())->orderByDesc('id')->get();
        $res['statuses'] = PlacementOffer::STATUSES;

        return is_mobile($request->input('type'), 'placement/student/offers', $res, 'view');
    }

    public function respondOffer(Request $request, OfferService $offers, $offer)
    {
        $data = $request->validate(['decision' => 'required|in:accepted,rejected', 'reason' => 'nullable|string|max:1000|required_if:decision,rejected']);
        $offer = PlacementOffer::where('student_id', $this->studentId())->findOrFail($offer);

        try {
            $offers->transition($offer, $data['decision'], $data['reason'] ?? null, true);
        } catch (ValidationException $e) {
            return redirect()->route('placement.student.offers')
                ->with('data', ['status_code' => 0, 'message' => collect($e->errors())->flatten()->implode(' ')]);
        }

        return redirect()->route('placement.student.offers')
            ->with('data', ['status_code' => 1, 'message' => 'Offer '.$data['decision'].'. The placement cell will verify your response.']);
    }

    /** Notice board plus the in-app notification inbox. */
    public function notices(Request $request)
    {
        $studentId = $this->studentId();
        $profile = $this->profiles->forStudent($studentId);
        $standardId = optional($this->students->find($studentId))->standard_id;

        $notices = PlacementAnnouncement::where('status', 1)
            ->where(fn ($q) => $q->whereNull('publish_at')->orWhere('publish_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderByDesc('is_pinned')->orderByDesc('id')->get()
            ->filter(function ($n) use ($profile, $standardId) {
                if ($n->audience === 'registered' && $profile->registration_status !== 'registered') {
                    return false;
                }
                if ($n->audience === 'verified' && ! $profile->isActive()) {
                    return false;
                }

                return empty($n->standard_ids) || in_array((int) $standardId, array_map('intval', $n->standard_ids), true);
            })->values();

        $inbox = PlacementNotificationLog::where(['student_id' => $studentId, 'channel' => 'inapp'])->orderByDesc('id')->limit(50)->get();

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['notices'] = $notices;
        $res['inbox'] = $inbox;

        $response = is_mobile($request->input('type'), 'placement/student/notices', $res, 'view');
        PlacementNotificationLog::where(['student_id' => $studentId, 'channel' => 'inapp'])->whereNull('read_at')->update(['read_at' => now()]);

        return $response;
    }

    // ------------------------------------------------------------ training, readiness, resume review

    public function training(Request $request, TrainingService $training)
    {
        $studentId = $this->studentId();

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['sessions'] = DB::table('placement_training_attendance as ta')->join('placement_training as t', 't.id', '=', 'ta.training_id')
            ->where('ta.sub_institute_id', session('sub_institute_id'))->where('ta.student_id', $studentId)->whereNull('t.deleted_at')
            ->orderByDesc('t.session_date')
            ->get(['t.title', 't.training_type', 't.trainer', 't.session_date', 't.start_time', 't.venue', 't.max_score', 't.status', 'ta.attendance', 'ta.score', 'ta.rating', 'ta.feedback']);
        $res['types'] = PlacementTraining::TYPES;
        $res['readiness'] = $training->readiness($studentId);
        $res['reviews'] = PlacementResumeReview::where('student_id', $studentId)->orderByDesc('id')->limit(5)->get();

        return is_mobile($request->input('type'), 'placement/student/training', $res, 'view');
    }

    public function requestReview(Request $request, AuditService $audit)
    {
        $data = $request->validate(['request_note' => 'nullable|string|max:1000']);
        $profile = $this->profiles->forStudent($this->studentId());

        if (empty($profile->resume_file)) {
            return redirect()->route('placement.student.training')->with('data', ['status_code' => 0, 'message' => 'Upload your resume on the profile page first.']);
        }
        if (PlacementResumeReview::where(['student_id' => $profile->student_id, 'status' => 'requested'])->exists()) {
            return redirect()->route('placement.student.training')->with('data', ['status_code' => 0, 'message' => 'You already have a review in progress.']);
        }

        $review = PlacementResumeReview::create(['student_id' => $profile->student_id, 'profile_id' => $profile->id, 'request_note' => $data['request_note'] ?? null, 'requested_at' => now()]);
        $audit->log('resume_review', $review->id, 'request');

        return redirect()->route('placement.student.training')->with('data', ['status_code' => 1, 'message' => 'Review requested. You will be notified when feedback is ready.']);
    }

    // ------------------------------------------------------------ helpers

    /** The student id always comes from the session, and only for student logins. */
    protected function studentId(): int
    {
        abort_unless(PlacementAccess::role() === PlacementAccess::STUDENT, 403, 'Students only.');

        return (int) session('user_id');
    }

    protected function cleanRows(array $rows, string $requiredKey): array
    {
        return array_values(array_filter($rows, fn ($r) => ! empty($r[$requiredKey])));
    }
}
