<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Public;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Inovector\Mixpost\Services\Bluesky\Crypt\PrivateKey;
use Inovector\Mixpost\SocialProviders\Bluesky\Concerns\UsesScopes;

class BlueskyOauthMetaController extends Controller
{
    use UsesScopes;

    public function clientMeta(): JsonResponse
    {
        return response()->json([
            'scope' => $this->formatScopes(),

            'application_type' => 'web',
            'grant_types' => ['authorization_code', 'refresh_token'],
            'response_types' => ['code'],
            'token_endpoint_auth_method' => 'private_key_jwt',
            'token_endpoint_auth_signing_alg' => PrivateKey::ALG,

            'client_id' => route('mixpost.blueskyOauth.clientMeta'),
            'jwks_uri' => route('mixpost.blueskyOauth.jwks'),
            'redirect_uris' => [route('mixpost.callbackSocialProvider', ['provider' => 'bluesky'])],

            'dpop_bound_access_tokens' => true,

            'client_name' => Config::get('app.name'),
            'client_uri' => Config::get('app.url'),
        ]);
    }

    public function jwks(): JsonResponse
    {
        $key = PrivateKey::load()->toJWK()->asPublic();

        return response()->json([
            'keys' => collect([$key])->toArray(),
        ]);
    }
}
