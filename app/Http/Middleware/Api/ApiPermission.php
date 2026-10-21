<?php

namespace App\Http\Middleware\Api;

use App\Http\Controllers\api\V1\BaseApiController as B;
use App\Services\Api\V1\MenuService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Usage: ->middleware(ApiPermission::class.':fees_collect.index,view'). Super Admin bypasses, as in checkPermission. */
class ApiPermission
{
    public function __construct(private MenuService $menus)
    {
    }

    public function handle(Request $request, Closure $next, string $menuLink, string $ability = 'view')
    {
        $ctx = $request->attributes->get('api_context');
        if (strcasecmp((string) $ctx['user_profile_name'], 'Super Admin') === 0) {
            return $next($request);
        }
        $menuId = DB::table('tblmenumaster')->where('status', 1)->where('link', $menuLink)->value('id');
        if ($menuId) {
            $perm = $this->menus->permissions($request->attributes->get('api_principal'))[(int) $menuId] ?? null;
            if (! $perm || empty($perm[$ability])) {
                return B::envelope(false, "You do not have permission to {$ability} this resource.", null, null, null, 403);
            }
        }

        return $next($request);
    }
}
