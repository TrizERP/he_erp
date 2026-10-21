<?php

namespace App\Http\Controllers\api\V1\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Standard list query for V1 endpoints: ?page &per_page (max 100) &search &sort (-field = descending) &filters[x].
 * Sort and search columns are whitelisted per endpoint, so nothing user-supplied reaches the SQL as a column name.
 */
trait PaginatesLists
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  array<string,string>  $sortable  public sort field => column
     * @param  string[]  $searchable  columns searched with LIKE
     */
    protected function listPage(Request $request, $query, array $sortable, array $searchable, string $defaultSort, callable $map): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        if ($search !== '' && $searchable) {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%';
            $query->where(function ($q) use ($searchable, $like) {
                foreach ($searchable as $col) {
                    $q->orWhere($col, 'like', $like);
                }
            });
        }

        $sort = (string) $request->query('sort', $defaultSort);
        $dir = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $field = ltrim($sort, '-');
        $column = $sortable[$field] ?? ($sortable[ltrim($defaultSort, '-')] ?? reset($sortable));
        $query->orderBy($column, $dir);

        $perPage = max(1, min(100, (int) $request->query('per_page', 25)));
        $page = max(1, (int) $request->query('page', 1));

        return $this->paginated($query->paginate($perPage, ['*'], 'page', $page), $map);
    }
}
