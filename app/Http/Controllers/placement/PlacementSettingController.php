<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Models\placement\PlacementAuditLog;
use App\Services\Placement\AuditService;
use App\Services\Placement\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use function App\Helpers\is_mobile;

/** Section 3L: module settings, notification toggles and the audit log viewer. */
class PlacementSettingController extends Controller
{
    public function index(Request $request, SettingService $settings)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['values'] = $settings->all();
        $res['labels'] = SettingService::LABELS;
        $res['entities'] = PlacementAuditLog::where('sub_institute_id', session('sub_institute_id'))->distinct()->orderBy('entity_type')->pluck('entity_type');

        return is_mobile($request->input('type'), 'placement/settings/index', $res, 'view');
    }

    public function update(Request $request, SettingService $settings, AuditService $audit)
    {
        $rules = ['reminder_hours_before' => 'required|integer|min:1|max:168'];
        foreach (array_keys(SettingService::DEFAULTS) as $key) {
            if (str_starts_with($key, 'notify_')) {
                $rules[$key] = 'nullable|in:1';
            }
        }
        $data = $request->validate($rules);

        $before = $settings->all();
        DB::transaction(function () use ($settings, $data, $before, $audit) {
            $after = [];
            foreach (array_keys(SettingService::DEFAULTS) as $key) {
                $value = str_starts_with($key, 'notify_') ? (isset($data[$key]) ? '1' : '0') : (string) $data[$key];
                $settings->set($key, $value);
                $after[$key] = $value;
            }
            [$old, $new] = $audit->diff($before, array_diff_assoc($after, $before));
            if ($new) {
                $audit->log('settings', null, 'update', $old, $new);
            }
        });

        return redirect()->route('placement.settings.index')->with('data', ['status_code' => 1, 'message' => 'Settings saved.']);
    }

    public function auditData(Request $request)
    {
        $query = DB::table('placement_audit_log as a')
            ->leftJoin('tbluser as u', 'u.id', '=', 'a.user_id')
            ->where('a.sub_institute_id', session('sub_institute_id'))
            ->when($request->filled('entity'), fn ($q) => $q->where('a.entity_type', $request->entity))
            ->selectRaw("a.id, a.created_at, a.entity_type, a.entity_id, a.action, a.reason,
                COALESCE(CONCAT_WS(' ', u.first_name, u.last_name), CONCAT('User #', a.user_id)) as user_name, a.old_values, a.new_values");

        return DataTables::query($query)
            ->addIndexColumn()
            ->editColumn('old_values', fn ($r) => e(mb_strimwidth((string) $r->old_values, 0, 120, '...')))
            ->editColumn('new_values', fn ($r) => e(mb_strimwidth((string) $r->new_values, 0, 120, '...')))
            ->editColumn('reason', fn ($r) => e($r->reason))
            ->toJson();
    }
}
