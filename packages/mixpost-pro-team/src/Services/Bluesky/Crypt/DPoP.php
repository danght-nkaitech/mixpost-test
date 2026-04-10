<?php

namespace Inovector\Mixpost\Services\Bluesky\Crypt;

use Firebase\JWT\JWT;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inovector\Mixpost\Concerns\UsesCacheKey;

final class DPoP
{
    use UsesCacheKey;

    public const AUTH_NONCE = 'dpop_auth_nonce';

    public const API_NONCE = 'dpop_api_nonce';

    protected const TYP = 'dpop+jwt';

    public static function generate(): string
    {
        return PrivateKey::create()->privateB64();
    }

    public static function load(string $key): JsonWebKey
    {
        return PrivateKey::load($key)->toJWK();
    }

    public static function authProof(
        JsonWebKey $jwk,
        string $url,
        ?string $nonce = '',
        string $method = 'POST',
    ): string {
        $head = [
            'typ' => self::TYP,
            'alg' => PrivateKey::ALG,
            'jwk' => $jwk->asPublic()->toArray(),
        ];

        $payload = [
            'nonce' => $nonce,
            'htm' => $method,
            'htu' => $url,
            'jti' => Str::random(64),
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addSeconds(600)->timestamp,
        ];

        return JsonWebToken::encode($head, $payload, $jwk->toPEM());
    }

    public static function apiProof(
        JsonWebKey $jwk,
        string $iss,
        string $url,
        string $token,
        ?string $nonce = '',
        string $method = 'POST',
    ): string {
        $head = [
            'typ' => self::TYP,
            'alg' => PrivateKey::ALG,
            'jwk' => $jwk->asPublic()->toArray(),
        ];

        $payload = [
            'nonce' => $nonce,
            'iss' => $iss,
            'htu' => $url,
            'htm' => $method,
            'jti' => Str::random(64),
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addSeconds(600)->timestamp,
            'ath' => self::createCodeChallenge($token),
        ];

        return JsonWebToken::encode($head, $payload, $jwk->toPEM());
    }

    protected static function createCodeChallenge(string $code): string
    {
        $hashed = hash('sha256', $code, true);

        return JWT::urlsafeB64Encode($hashed);
    }
}
