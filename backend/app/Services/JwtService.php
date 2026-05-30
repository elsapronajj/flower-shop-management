<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class JwtService
{
    public function createAccessToken(User $user): string
    {
        $now = now();
        $payload = [
            'iss' => config('app.url'),
            'sub' => (string) $user->id,
            'iat' => $now->timestamp,
            'exp' => $now->copy()->addMinutes(config('tokens.access_ttl_minutes'))->timestamp,
            'jti' => (string) Str::uuid(),
        ];

        return $this->encode($payload);
    }

    public function decode(string $jwt): array
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid token.');
        }

        [$header, $payload, $signature] = $parts;
        $expected = $this->base64UrlEncode(hash_hmac('sha256', $header.'.'.$payload, $this->secret(), true));

        if (! hash_equals($expected, $signature)) {
            throw new RuntimeException('Invalid token signature.');
        }

        $decodedHeader = json_decode($this->base64UrlDecode($header), true);
        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);

        if (($decodedHeader['alg'] ?? null) !== 'HS256' || ! is_array($decodedPayload)) {
            throw new RuntimeException('Invalid token.');
        }

        if (($decodedPayload['exp'] ?? 0) < now()->timestamp) {
            throw new RuntimeException('Token expired.');
        }

        return $decodedPayload;
    }

    private function encode(array $payload): string
    {
        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'], JSON_THROW_ON_ERROR));
        $body = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', $header.'.'.$body, $this->secret(), true));

        return $header.'.'.$body.'.'.$signature;
    }

    private function secret(): string
    {
        $secret = (string) config('tokens.jwt_secret');

        if (str_starts_with($secret, 'base64:')) {
            return base64_decode(substr($secret, 7)) ?: $secret;
        }

        return $secret;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }
}
