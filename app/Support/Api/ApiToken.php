<?php

namespace App\Support\Api;

/**
 * Stateless HS256 bearer token for the V1 API (no token table needed, so nothing has to be migrated).
 * Signed with APP_KEY. Payload: sub (user id), kind (user|student), iat, exp.
 */
class ApiToken
{
    public static function issue(int $id, string $kind, int $ttlSeconds = 28800): string
    {
        $now = time();

        return self::encode(['sub' => $id, 'kind' => $kind, 'iat' => $now, 'exp' => $now + $ttlSeconds]);
    }

    /** @return array|null payload, or null when the token is malformed, tampered with or expired */
    public static function verify(?string $token): ?array
    {
        $parts = explode('.', (string) $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$h, $p, $s] = $parts;
        if (! hash_equals(self::sign("$h.$p"), $s)) {
            return null;
        }
        $payload = json_decode(self::b64d($p), true);
        if (! is_array($payload) || ($payload['exp'] ?? 0) < time() || ! isset($payload['sub'], $payload['kind'])) {
            return null;
        }

        return $payload;
    }

    private static function encode(array $payload): string
    {
        $h = self::b64e(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $p = self::b64e(json_encode($payload));

        return "$h.$p.".self::sign("$h.$p");
    }

    private static function sign(string $data): string
    {
        $key = (string) config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return self::b64e(hash_hmac('sha256', $data, $key, true));
    }

    private static function b64e(string $v): string
    {
        return rtrim(strtr(base64_encode($v), '+/', '-_'), '=');
    }

    private static function b64d(string $v): string
    {
        return (string) base64_decode(strtr($v, '-_', '+/'));
    }
}
