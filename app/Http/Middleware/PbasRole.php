<?php

namespace App\Http\Middleware;

use App\Services\Pbas\PbasAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Usage: ->middleware('pbas.role:faculty')  or  ('pbas.role:hod,principal')  (ERP admins always pass).
 */
class PbasRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (PbasAccess::allows($roles)) {
            return $next($request);
        }

        if ($request->input('type') === 'API' || $request->expectsJson()) {
            return response()->json(['status' => 0, 'message' => 'You are not authorised for this PBAS action.'], 403);
        }

        abort(403, 'You are not authorised for this PBAS action.');
    }
}
