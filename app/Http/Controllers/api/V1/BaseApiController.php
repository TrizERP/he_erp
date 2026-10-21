<?php

namespace App\Http\Controllers\api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/** Every V1 response uses { success, message, data, meta, errors }. */
abstract class BaseApiController extends Controller
{
    public static function envelope(bool $success, string $message, $data = null, ?array $meta = null, $errors = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
            'errors' => $errors,
        ], $status);
    }

    protected function ok($data = null, string $message = 'Success', ?array $meta = null, int $status = 200): JsonResponse
    {
        return self::envelope(true, $message, $data, $meta, null, $status);
    }

    protected function fail(string $message, $errors = null, int $status = 400): JsonResponse
    {
        return self::envelope(false, $message, null, null, $errors, $status);
    }

    /** @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $p */
    protected function paginated($p, ?callable $map = null, string $message = 'Success'): JsonResponse
    {
        $items = $map ? collect($p->items())->map($map)->all() : $p->items();

        return $this->ok($items, $message, ['page' => $p->currentPage(), 'per_page' => $p->perPage(), 'total' => $p->total()]);
    }
}
