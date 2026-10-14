<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Http\Requests\placement\PlacementComposeRequest;
use App\Jobs\Placement\SendPlacementNotification;
use App\Models\placement\PlacementDrive;
use App\Models\placement\PlacementNotificationLog;
use App\Models\placement\PlacementStudentProfile;
use App\Models\placement\PlacementTemplate;
use App\Services\Placement\NotificationService;
use App\Services\Placement\StudentLookup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use function App\Helpers\is_mobile;

/** Section 3I: bulk communication from templates, and the notification log with delivery status. */
class PlacementNotificationController extends Controller
{
    public function index(Request $request)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['templates'] = PlacementTemplate::where('status', 1)->orderBy('channel')->orderBy('name')->get();
        $res['drives'] = PlacementDrive::whereNotIn('status', ['draft', 'cancelled'])->orderByDesc('id')->limit(200)->pluck('title', 'id');
        $res['standards'] = DB::table('standard')->where('sub_institute_id', session('sub_institute_id'))->orderBy('sort_order')->pluck('name', 'id');
        $res['counts'] = PlacementNotificationLog::select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');

        return is_mobile($request->input('type'), 'placement/notification/index', $res, 'view');
    }

    public function data(Request $request)
    {
        $query = DB::table('placement_notification_log as n')
            ->leftJoin('tblstudent as s', 's.id', '=', 'n.student_id')
            ->where('n.sub_institute_id', session('sub_institute_id'))
            ->when($request->filled('channel'), fn ($q) => $q->where('n.channel', $request->channel))
            ->when($request->filled('status'), fn ($q) => $q->where('n.status', $request->status))
            ->selectRaw("n.id, n.created_at, s.enrollment_no, CONCAT_WS(' ', s.first_name, s.last_name) as student,
                n.channel, n.event_key, n.recipient, n.subject, n.status, n.error");

        return DataTables::query($query)
            ->addIndexColumn()
            ->editColumn('status', fn ($r) => '<span class="label label-'.['queued' => 'default', 'sent' => 'success', 'failed' => 'danger', 'skipped' => 'warning'][$r->status].'">'.ucfirst($r->status).'</span>')
            ->editColumn('error', fn ($r) => e($r->error))
            ->addColumn('action', fn ($r) => $r->status === 'failed'
                ? '<form method="post" action="'.route('placement.notifications.resend', $r->id).'">'.csrf_field().'<button class="btn btn-xs btn-warning">Resend</button></form>' : '')
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function send(PlacementComposeRequest $request)
    {
        $ids = $this->audience($request);
        if (! $ids) {
            return redirect()->route('placement.notifications.index')->with('data', ['status_code' => 0, 'message' => 'No students match that audience.']);
        }

        $vars = ['date' => now()->format('d-m-Y')];
        if ($request->filled('drive_id')) {
            $drive = PlacementDrive::with('company')->findOrFail($request->drive_id);
            $vars += [
                'company' => $drive->company->name ?? '', 'date' => $drive->drive_date?->format('d-m-Y') ?? $vars['date'],
                'venue' => $drive->venue ?? '', 'deadline' => $drive->registration_deadline?->format('d-m-Y H:i') ?? '',
            ];
        }

        $n = app(NotificationService::class)->sendTemplates($request->template_ids, $ids, $vars);

        return redirect()->route('placement.notifications.index')
            ->with('data', ['status_code' => 1, 'message' => "{$n} message(s) queued for ".count($ids).' student(s).']);
    }

    public function resend($id)
    {
        $log = PlacementNotificationLog::where('status', 'failed')->findOrFail($id);
        $log->update(['status' => 'queued', 'error' => null]);
        SendPlacementNotification::dispatch($log->id);

        return redirect()->route('placement.notifications.index')->with('data', ['status_code' => 1, 'message' => 'Queued for resend.']);
    }

    /** @return int[] */
    protected function audience(PlacementComposeRequest $request): array
    {
        $driveId = (int) $request->drive_id;

        $ids = match ($request->audience) {
            'registered' => PlacementStudentProfile::where('registration_status', 'registered')->pluck('student_id'),
            'verified' => PlacementStudentProfile::where('registration_status', 'registered')->where('verification_status', 'verified')->pluck('student_id'),
            'drive_eligible' => DB::table('placement_drive_eligible_student')->where('sub_institute_id', session('sub_institute_id'))
                ->where(['drive_id' => $driveId, 'is_eligible' => 1])->pluck('student_id'),
            'drive_applicants' => DB::table('placement_application')->where('sub_institute_id', session('sub_institute_id'))->whereNull('deleted_at')
                ->where('drive_id', $driveId)->whereIn('status', ['applied', 'shortlisted', 'selected'])->pluck('student_id'),
            'drive_shortlisted' => DB::table('placement_application')->where('sub_institute_id', session('sub_institute_id'))->whereNull('deleted_at')
                ->where(['drive_id' => $driveId, 'status' => 'shortlisted'])->pluck('student_id'),
        };
        $ids = $ids->unique()->values()->all();

        if ($request->filled('standard_ids') && $ids) {
            $ids = app(StudentLookup::class)->students($ids, array_map('intval', $request->standard_ids))->keys()->all();
        }

        return $ids;
    }
}
