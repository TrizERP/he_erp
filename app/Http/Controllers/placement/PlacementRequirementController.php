<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Models\placement\PlacementCompanyRequirement;
use App\Models\placement\PlacementDrive;
use App\Models\placement\PlacementSeason;
use App\Services\Placement\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use function App\Helpers\is_mobile;

/** TPO review of requirements posted by recruiters through the portal. Accepting creates a draft drive. */
class PlacementRequirementController extends Controller
{
    public function index(Request $request)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['counts'] = PlacementCompanyRequirement::select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');

        return is_mobile($request->input('type'), 'placement/requirement/index', $res, 'view');
    }

    public function data(Request $request)
    {
        $query = DB::table('placement_company_requirement as q')->join('placement_company as c', 'c.id', '=', 'q.company_id')
            ->where('q.sub_institute_id', session('sub_institute_id'))->whereNull('q.deleted_at')
            ->when($request->filled('status'), fn ($q) => $q->where('q.status', $request->status))
            ->select('q.id', 'c.name as company', 'q.title', 'q.openings', 'q.ctc_range', 'q.skills', 'q.contact_name', 'q.status', 'q.created_at', 'q.description', 'q.drive_id');

        return DataTables::query($query)
            ->addIndexColumn()
            ->editColumn('title', fn ($r) => '<strong>'.e($r->title).'</strong><br><small>'.e(mb_strimwidth((string) $r->description, 0, 160, '...')).'</small>')
            ->editColumn('status', fn ($r) => '<span class="label label-'.['pending' => 'warning', 'accepted' => 'success', 'rejected' => 'danger'][$r->status].'">'.ucfirst($r->status).'</span>')
            ->addColumn('action', function ($r) {
                if ($r->status === 'accepted' && $r->drive_id) {
                    return '<a class="btn btn-xs btn-info" href="'.route('placement.drives.show', $r->drive_id).'">Open drive</a>';
                }
                if ($r->status !== 'pending') {
                    return '';
                }
                $csrf = csrf_field();

                return '<form method="post" action="'.route('placement.requirements.decide', $r->id).'" style="display:inline">'.$csrf
                    .'<button name="decision" value="accepted" class="btn btn-xs btn-success">Accept &amp; draft drive</button> '
                    .'<button name="decision" value="rejected" class="btn btn-xs btn-danger" onclick="return confirm(\'Reject this requirement?\')">Reject</button></form>';
            })
            ->rawColumns(['title', 'status', 'action'])
            ->toJson();
    }

    public function decide(Request $request, AuditService $audit, $requirement)
    {
        $data = $request->validate(['decision' => 'required|in:accepted,rejected', 'review_note' => 'nullable|string|max:1000']);
        $req = PlacementCompanyRequirement::where('status', 'pending')->findOrFail($requirement);

        DB::transaction(function () use ($req, $data, $audit) {
            if ($data['decision'] === 'accepted') {
                $drive = PlacementDrive::create([
                    'company_id' => $req->company_id, 'season_id' => PlacementSeason::where('is_current', 1)->value('id'),
                    'title' => $req->title, 'description' => trim($req->description."\n\nSkills: ".$req->skills."\nCTC: ".$req->ctc_range), 'status' => 'draft',
                ]);
                $drive->roles()->create(['title' => $req->title, 'openings' => $req->openings]);
                $req->drive_id = $drive->id;
            }
            $req->status = $data['decision'];
            $req->reviewed_by = session('user_id');
            $req->review_note = $data['review_note'] ?? null;
            $req->save();
            $audit->log('company_requirement', $req->id, $data['decision']);
        });

        return redirect()->route('placement.requirements.index')->with('data', ['status_code' => 1, 'message' => 'Requirement '.$data['decision'].'.']);
    }
}
