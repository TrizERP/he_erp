<?php

namespace App\Http\Middleware;

use App\Services\Admission\AdmissionAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Usage: ->middleware('admission.role:manage,decide')  (passes if the user holds ANY listed capability;
 * ERP admins always pass). Authorisation is enforced here on the server, not by hiding menu items.
 */
class AdmissionRole
{
    public function handle(Request $request, Closure $next, ...$capabilities)
    {
        if (AdmissionAccess::canAny($capabilities)) {
            return $next($request);
        }

        if ($request->input('type') === 'API' || $request->expectsJson()) {
            return response()->json(['status' => 0, 'message' => 'You are not authorised for this admission action.'], 403);
        }

        abort(403, 'You are not authorised for this admission action.');
    }
}
