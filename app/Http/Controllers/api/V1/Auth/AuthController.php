<?php

namespace App\Http\Controllers\api\V1\Auth;

use App\Http\Controllers\api\V1\BaseApiController;
use App\Services\Api\V1\AuthService;
use App\Services\Api\V1\ContextService;
use App\Services\Api\V1\MenuService;
use App\Support\Api\ApiToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends BaseApiController
{
    public function __construct(
        private AuthService $auth,
        private ContextService $contexts,
        private MenuService $menus,
    ) {
    }

    /** POST /auth/login  { email, password } */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|string', 'password' => 'required|string']);

        $found = $this->auth->attempt($data['email'], $data['password']);
        if (! $found) {
            return $this->fail('Invalid User Id And Password', null, 401);
        }
        $principal = $this->contexts->principal($found['id'], $found['kind']);
        $context = $this->contexts->resolve($principal);
        if (! $context || ! $context['institute_id']) {
            return $this->fail('No institute is assigned to this user', null, 403);
        }
        if (! $context['syear']) {
            return $this->fail('Academic Term Date Expired', null, 403);
        }
        if (! $this->menus->permissions($principal)) {
            return $this->fail('Please Contact Administrator For ERP Rights', null, 403);
        }

        $token = ApiToken::issue($found['id'], $found['kind']);

        return $this->ok(['token' => $token] + $this->profilePayload($principal, $context), 'User Successfully Login');
    }

    /** POST /auth/logout. Tokens are stateless; the frontend discards its cookie. */
    public function logout(): JsonResponse
    {
        return $this->ok(null, 'Logged out');
    }

    /** GET /auth/me. Reflects the context chosen with X-Institute-Id / X-Academic-Year / X-Term-Id. */
    public function me(Request $request): JsonResponse
    {
        return $this->ok($this->profilePayload($request->attributes->get('api_principal'), $request->attributes->get('api_context')));
    }

    /** GET /auth/menu */
    public function menu(Request $request): JsonResponse
    {
        return $this->ok($this->menus->tree($request->attributes->get('api_principal'), $request->attributes->get('api_context')));
    }

    private function profilePayload(array $principal, array $context): array
    {
        $years = DB::table('academic_year')->where('sub_institute_id', $context['institute_id'])->orderBy('sort_order')->get();

        return [
            'user' => [
                'id' => $context['user_id'],
                'kind' => $context['kind'],
                'name' => $context['name'],
                'email' => $context['email'],
                'image' => $principal['image'] ?? null,
                'profile_id' => $context['user_profile_id'],
                'profile' => $context['user_profile_name'],
                'is_admin' => $context['is_admin'] === 1,
            ],
            'context' => $context,
            'institutes' => $this->contexts->institutes($principal),
            'academic_years' => $years->pluck('syear')->unique()->values()->all(),
            'terms' => $years->where('syear', $context['syear'])->map(fn ($r) => [
                'term_id' => $r->term_id, 'title' => $r->title ?? null,
            ])->values()->all(),
            'menu' => $this->menus->tree($principal, $context),
            'setup_menu' => $this->menus->setupTree($principal, $context),
        ];
    }
}
