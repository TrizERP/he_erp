<?php

namespace App\Http\Controllers\placement;

use App\Exports\Placement\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\placement\PlacementCompany;
use App\Models\placement\PlacementDrive;
use App\Models\placement\PlacementOffer;
use App\Models\placement\PlacementSeason;
use App\Services\Placement\AuditService;
use App\Services\Placement\PlacementAccess;
use App\Services\Placement\ReportService;
use App\Services\Placement\StudentLookup;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use function App\Helpers\is_mobile;

/** Section 6: report catalogue, DataTable view and Excel / PDF export. */
class PlacementReportController extends Controller
{
    protected ReportService $reports;
    protected StudentLookup $students;

    public function __construct(ReportService $reports, StudentLookup $students)
    {
        $this->reports = $reports;
        $this->students = $students;
    }

    public function index(Request $request)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['reports'] = collect($this->reports->definitions())->filter(fn ($d) => PlacementAccess::allows($d['roles']));

        return is_mobile($request->input('type'), 'placement/reports/index', $res, 'view');
    }

    public function show(Request $request, $key)
    {
        $def = $this->definition($key);

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['key'] = $key;
        $res['def'] = $def;
        $res['filters'] = $this->filters($request, $def);
        $res['options'] = $this->options($key, $def);
        // Column headings (and a summary) come from a cheap build; the rows load via AJAX.
        $built = $this->reports->build($key, $this->filters($request, $def, true));
        $res['columns'] = collect($built['columns'])->map(fn ($c) => is_array($c) ? $c[0] : $c)->all();
        $res['summary'] = $built['summary'] ?? [];
        $res['server_side'] = isset($built['query']);

        return is_mobile($request->input('type'), 'placement/reports/show', $res, 'view');
    }

    public function data(Request $request, $key)
    {
        $def = $this->definition($key);
        $built = $this->reports->build($key, $this->filters($request, $def, true));

        if (isset($built['rows'])) {
            return DataTables::collection($built['rows'])->addIndexColumn()->toJson();
        }

        $table = DataTables::query($built['query'])->addIndexColumn();
        foreach ($built['columns'] as $col => $c) {
            $expr = $c[1];
            $format = $c[2] ?? null;
            $table->filterColumn($col, fn ($q, $kw) => $q->whereRaw("{$expr} LIKE ?", ['%'.$kw.'%']));
            $table->orderColumn($col, "{$expr} \$1");
            if ($format) {
                $table->editColumn($col, fn ($row) => $format($row->{$col}));
            }
        }

        return $table->toJson();
    }

    public function export(Request $request, AuditService $audit, $key, $format)
    {
        abort_unless(in_array($format, ['excel', 'pdf'], true), 404);
        $def = $this->definition($key);
        $filters = $this->filters($request, $def, true);
        $built = $this->reports->build($key, $filters);

        [$headings, $rows] = $this->flatten($built);
        $audit->log('report', null, 'export_'.$format, null, ['report' => $key, 'filters' => $request->only($def['filters']), 'rows' => count($rows)]);
        $file = $key.'_'.now()->format('Ymd_His');

        if ($format === 'excel') {
            return Excel::download(new ReportExport($def['title'], $headings, $rows), $file.'.xlsx');
        }

        return Pdf::loadView('placement.reports.pdf', [
            'title' => $def['title'], 'headings' => $headings, 'rows' => $rows, 'summary' => $built['summary'] ?? [],
            'filters' => $this->filterLabels($request, $def), 'institute' => session('school_name') ?? '',
        ])->setPaper('a4', 'landscape')->download($file.'.pdf');
    }

    // ------------------------------------------------------------ helpers

    protected function definition(string $key): array
    {
        $def = $this->reports->definitions()[$key] ?? null;
        abort_unless($def && PlacementAccess::allows($def['roles']), $def ? 403 : 404);

        return $def;
    }

    /** Whitelisted, typed filters. $internal adds the coordinator course scope (never user-supplied). */
    protected function filters(Request $request, array $def, bool $internal = false): array
    {
        $f = [];
        foreach ($def['filters'] as $name) {
            $v = $request->input($name);
            if ($v === null || $v === '') {
                continue;
            }
            $f[$name] = in_array($name, ['season_id', 'standard_id', 'drive_id', 'company_id', 'passing_year'], true) ? (int) $v : (string) $v;
        }
        if ($internal && ($scope = $this->students->scopeStandardIds()) !== null) {
            $f['standard_ids'] = $scope;
        }

        return $f;
    }

    /** @return array{0:string[],1:array[]} headings and plain rows */
    protected function flatten(array $built): array
    {
        $headings = collect($built['columns'])->map(fn ($c) => is_array($c) ? $c[0] : $c)->values()->all();
        $keys = array_keys($built['columns']);
        $rows = [];

        if (isset($built['rows'])) {
            foreach ($built['rows'] as $r) {
                $rows[] = array_map(fn ($k) => $r[$k] ?? '', $keys);
            }
        } else {
            foreach ($built['query']->get() as $r) {
                $rows[] = array_map(function ($k) use ($built, $r) {
                    $format = $built['columns'][$k][2] ?? null;

                    return $format ? $format($r->{$k}) : $r->{$k};
                }, $keys);
            }
        }

        return [$headings, $rows];
    }

    protected function filterLabels(Request $request, array $def): array
    {
        $labels = [];
        foreach ($this->filters($request, $def) as $name => $value) {
            $labels[ucwords(str_replace('_', ' ', preg_replace('/_id$/', '', $name)))] = $this->optionLabel($name, $value);
        }

        return $labels;
    }

    protected function optionLabel(string $name, $value): string
    {
        return match ($name) {
            'season_id'   => PlacementSeason::where('id', $value)->value('name') ?? $value,
            'standard_id' => DB::table('standard')->where('id', $value)->value('name') ?? $value,
            'drive_id'    => PlacementDrive::where('id', $value)->value('title') ?? $value,
            'company_id'  => PlacementCompany::where('id', $value)->value('name') ?? $value,
            default       => (string) $value,
        };
    }

    protected function options(string $key, array $def): array
    {
        $o = [];
        $need = fn ($f) => in_array($f, $def['filters'], true);
        if ($need('season_id')) {
            $o['season_id'] = PlacementSeason::orderByDesc('start_date')->pluck('name', 'id')->all();
        }
        if ($need('standard_id')) {
            $scope = $this->students->scopeStandardIds();
            $o['standard_id'] = DB::table('standard')->where('sub_institute_id', session('sub_institute_id'))
                ->when($scope !== null, fn ($q) => $q->whereIn('id', $scope ?: [0]))->orderBy('sort_order')->pluck('name', 'id')->all();
        }
        if ($need('drive_id')) {
            $o['drive_id'] = PlacementDrive::where('status', '!=', 'draft')->orderByDesc('id')->limit(300)->pluck('title', 'id')->all();
        }
        if ($need('company_id')) {
            $o['company_id'] = PlacementCompany::orderBy('name')->pluck('name', 'id')->all();
        }
        if ($need('status')) {
            $o['status'] = match ($key) {
                'company_wise'    => PlacementCompany::STATUSES,
                'eligibility'     => ['eligible' => 'Eligible', 'not_eligible' => 'Not eligible'],
                'offers'          => PlacementOffer::STATUSES,
                'communication'   => ['queued' => 'Queued', 'sent' => 'Sent', 'failed' => 'Failed', 'skipped' => 'Skipped'],
                'student_profile' => ['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected'],
                default           => [],
            };
        }
        if ($need('placed')) {
            $o['placed'] = ['placed' => 'Placed', 'unplaced' => 'Unplaced'];
        }

        return $o;
    }
}
