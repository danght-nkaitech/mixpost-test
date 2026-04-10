<?php

namespace Inovector\Mixpost\Services\Bluesky\Crypt;

use Firebase\JWT\JWT;
use Illuminate\Support\Str;

final class JsonWebToken
{
    public static function encode(array $head, array $payload, string $key, string $alg = PrivateKey::ALG): string
    {
        return JWT::encode(
            payload: $payload,
            key: $key,
            alg: $alg,
            head: $head,
        );
    }

    public static function explode(?string $token, bool $decode = false): array
    {
        if (is_null($token) || Str::substrCount($token, '.') !== 2) {
            return [[], [], ''];
        }

        [$header, $payload, $sig] = explode(separator: '.', string: $token, limit: 3);

        $header = json_decode(JWT::urlsafeB64Decode($header), associative: true, flags: JSON_BIGINT_AS_STRING);
        $payload = json_decode(JWT::urlsafeB64Decode($payload), associative: true, flags: JSON_BIGINT_AS_STRING);
        if ($decode) {
            $sig = JWT::urlsafeB64Decode($sig);
        }

        return [$header, $payload, $sig];
    }
}
