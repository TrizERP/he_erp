<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Http\Requests\placement\PlacementCrudRequest;
use App\Services\Placement\AuditService;
use App\Services\Placement\PlacementAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * Config-driven list + modal CRUD used by the simple placement setup screens.
 * Subclasses describe the model, columns and form fields; this class does the
 * server-side DataTable, validation (via PlacementCrudRequest), transactions and audit.
 *
 * Authorization is route-level (placement.role middleware); tenant scoping is the
 * PlacementModel global scope, so every query here is already limited to the institute.
 */
abstract class PlacementCrudController extends Controller
{
    /** @return class-string<Model> */
    abstract protected function model(): string;

    abstract protected function title(): string;

    /** @return array<string,string> column key => heading */
    abstract protected function columns(): array;

    /**
     * @return array<int,array{name:string,label:string,type:string,options?:array,required?:bool,help?:string}>
     * types: text, number, date, textarea, select, multiselect
     */
    abstract protected function fields(): array;

    abstract public function rules(Request $request, ?int $id = null): array;

    /** Route name prefix, e.g. placement.seasons => placement.seasons.data / .store ... */
    abstract protected function routePrefix(): string;

    protected function baseQuery(Request $request): Builder
    {
        return ($this->model())::query();
    }

    /** Extra values merged into the validated data on create/update. */
    protected function extraData(Request $request): array
    {
        return [];
    }

    /** Hook for derived column values in the DataTable. */
    protected function decorate($datatable)
    {
        return $datatable;
    }

    /** Extra per-row buttons: <button class="pl-act" data-url=".." data-confirm="..">. */
    protected function extraActions($row): string
    {
        return '';
    }

    protected function indexData(Request $request): array
    {
        return [];
    }

    public function index(Request $request)
    {
        $data = array_merge([
            'title'      => $this->title(),
            'columns'    => $this->columns(),
            'fields'     => $this->fields(),
            'prefix'     => $this->routePrefix(),
            'query'      => $request->only('type'),
            'canWrite'   => $this->canWrite(),
            'canDelete'  => $this->canWrite(),
        ], $this->indexData($request));

        return view('placement.crud.index', ['data' => $data]);
    }

    public function data(Request $request)
    {
        $query = $this->baseQuery($request);
        $canWrite = $this->canWrite();
        $keys = array_keys($this->columns());
        $fieldMeta = collect($this->fields())->keyBy('name');

        $table = DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) use ($canWrite) {
                $html = $this->extraActions($row);
                if (! $canWrite) {
                    return $html;
                }

                return $html.'<button class="btn btn-xs btn-info pl-edit" data-id="'.$row->id.'"><i class="fa fa-pencil"></i></button> '
                    .'<button class="btn btn-xs btn-danger pl-delete" data-id="'.$row->id.'"><i class="fa fa-trash"></i></button>';
            });

        // Render select options as their label instead of the stored key.
        foreach ($keys as $key) {
            $meta = $fieldMeta->get($key);
            if ($meta && in_array($meta['type'], ['select'], true) && ! empty($meta['options'])) {
                $table->editColumn($key, fn ($row) => $meta['options'][$row->{$key}] ?? $row->{$key});
            }
            if ($meta && $meta['type'] === 'multiselect') {
                $table->editColumn($key, function ($row) use ($key, $meta) {
                    return collect((array) $row->{$key})->map(fn ($v) => $meta['options'][$v] ?? $v)->implode(', ');
                });
            }
        }

        return $this->decorate($table)->rawColumns(['action'])->toJson();
    }

    public function edit(Request $request, $id)
    {
        return response()->json($this->find($request, $id));
    }

    public function store(PlacementCrudRequest $request)
    {
        $model = DB::transaction(function () use ($request) {
            $row = ($this->model())::create(array_merge($request->validated(), $this->extraData($request)));
            app(AuditService::class)->log($this->auditName(), $row->id, 'create', null, $row->only(array_keys($request->validated())));

            return $row;
        });

        return response()->json(['status' => 1, 'message' => 'Saved successfully.', 'id' => $model->id]);
    }

    public function update(PlacementCrudRequest $request, $id)
    {
        $row = $this->find($request, $id);

        DB::transaction(function () use ($request, $row) {
            $changes = array_merge($request->validated(), $this->extraData($request));
            $row->fill($changes);
            [$old, $new] = app(AuditService::class)->diff($row->getOriginal(), $row->getDirty());
            $row->save();

            if ($new) {
                app(AuditService::class)->log($this->auditName(), $row->id, 'update', $old, $new);
            }
        });

        return response()->json(['status' => 1, 'message' => 'Updated successfully.']);
    }

    public function destroy(Request $request, $id)
    {
        $row = $this->find($request, $id);

        DB::transaction(function () use ($row) {
            $row->delete();
            app(AuditService::class)->log($this->auditName(), $row->id, 'delete');
        });

        return response()->json(['status' => 1, 'message' => 'Deleted.']);
    }

    protected function find(Request $request, $id): Model
    {
        return $this->baseQuery($request)->findOrFail($id);
    }

    protected function auditName(): string
    {
        return str_replace('placement.', '', $this->routePrefix());
    }

    protected function canWrite(): bool
    {
        return PlacementAccess::allows([PlacementAccess::TPO]);
    }

    /** Options for the standard (course) pickers, scoped to the institute. */
    protected function standardOptions(): array
    {
        return DB::table('standard')
            ->where('sub_institute_id', session()->get('sub_institute_id'))
            ->orderBy('sort_order')
            ->pluck('name', 'id')
            ->toArray();
    }
}
