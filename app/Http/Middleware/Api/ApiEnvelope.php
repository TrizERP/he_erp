<?php

namespace App\Http\Middleware\Api;

use App\Http\Controllers\api\V1\BaseApiController as B;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes every api/v1 response use the standard envelope without touching the web app's exception handler.
 * Laravel's routing pipeline renders exceptions into responses before they reach a middleware catch block,
 * so errors are normalised from the rendered JSON response here.
 */
class ApiEnvelope
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        $response = $next($request);

        if (! $response instanceof JsonResponse) {
            return $response;
        }
        $body = $response->getData(true);
        if (is_array($body) && array_key_exists('success', $body)) {
            return $response; // already enveloped by a controller / middleware
        }

        $status = $response->getStatusCode();
        if ($status < 400) {
            return B::envelope(true, 'Success', $body, null, null, $status);
        }

        $message = is_array($body) ? ($body['message'] ?? null) : null;
        $errors = is_array($body) ? ($body['errors'] ?? null) : null;
        if ($status === 422) {
            $message = 'Validation failed';
        } elseif ($status >= 500 && ! config('app.debug')) {
            $message = 'Server error';
        } elseif (! $message) {
            $message = Response::$statusTexts[$status] ?? 'Error';
        }

        return B::envelope(false, $message, null, null, $errors, $status);
    }
}
