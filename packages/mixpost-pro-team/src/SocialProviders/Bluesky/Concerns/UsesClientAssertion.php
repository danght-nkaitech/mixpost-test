<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inovector\Mixpost\Services\Bluesky\Crypt\JsonWebToken;
use Inovector\Mixpost\Services\Bluesky\Crypt\PrivateKey;

trait UsesClientAssertion
{
    protected const CLIENT_ASSERTION_TYPE = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';

    protected function getClientAssertion(): string
    {
        $key = PrivateKey::load();

        $head = [
            'alg' => $key::ALG,
            'kid' => $key->toJWK()->kid(),
        ];

        $payload = [
            'iss' => $this->clientId,
            'sub' => $this->clientId,
            'aud' => $this->values['data']['server'],
            'jti' => Str::random(40),
            'iat' => Carbon::now()->timestamp,
        ];

        return JsonWebToken::encode($head, $payload, $key->privatePEM());
    }
}
