<?php

namespace App\Http\Middleware\Api;

use App\Http\Controllers\api\V1\BaseApiController as B;
use App\Services\Api\V1\ContextService;
use App\Support\Api\ApiToken;
use Closure;
use Illuminate\Http\Request;

/**
 * Bearer-token auth + context resolution. Context comes from headers X-Institute-Id / X-Academic-Year / X-Term-Id
 * (validated against what the user may access) and is mirrored into an array session for legacy helpers.
 * Sets request attributes: api_principal, api_context.
 */
class ApiAuth
{
    public function __construct(private ContextService $contexts)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $payload = ApiToken::verify($request->bearerToken());
        $principal = $payload ? $this->contexts->principal((int) $payload['sub'], $payload['kind']) : null;
        if (! $principal) {
            return B::envelope(false, 'Unauthenticated', null, null, null, 401);
        }

        $context = $this->contexts->resolve($principal, [
            'institute_id' => $request->header('X-Institute-Id'),
            'syear' => $request->header('X-Academic-Year'),
            'term_id' => $request->header('X-Term-Id'),
        ]);
        if (! $context) {
            return B::envelope(false, 'Institute not allowed for this user', null, null, null, 403);
        }

        config(['session.driver' => 'array']);
        $this->contexts->applyToSession($context, $principal);
        $request->attributes->set('api_principal', $principal);
        $request->attributes->set('api_context', $context);

        return $next($request);
    }
}
