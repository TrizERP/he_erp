<?php

namespace App\Http\Middleware;

use App\Services\Placement\PlacementAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Usage: ->middleware('placement.role:tpo,principal')  (ERP admins always pass).
 */
class PlacementRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (PlacementAccess::allows($roles)) {
            return $next($request);
        }

        if ($request->input('type') === 'API' || $request->expectsJson()) {
            return response()->json(['status' => 0, 'message' => 'You are not authorised for this placement action.'], 403);
        }

        abort(403, 'You are not authorised for this placement action.');
    }
}
