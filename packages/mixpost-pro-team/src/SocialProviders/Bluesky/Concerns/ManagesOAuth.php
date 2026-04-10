<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Concerns\OAuth\UsesOAuthDPoPSession;
use Inovector\Mixpost\Concerns\UsesCacheKey;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Exceptions\OAuthInvalidGrant;
use Inovector\Mixpost\Mixpost;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait ManagesOAuth
{
    use UsesAccessToken;
    use UsesAuthServer;
    use UsesCacheKey;
    use UsesClientAssertion;
    use UsesOAuthDPoPSession;
    use UsesPAR;
    use UsesTokenRequest;

    protected ?string $service = null;

    public function getAuthUrl(): string
    {
        $this->newDPoPKeySession($this->request);

        $parRequestUri = $this->getParRequestUrl();

        $authorizationEndpoint = $this->authServerMeta('authorization_endpoint');

        return $this->buildUrlFromBase($authorizationEndpoint, [
            'client_id' => $this->clientId,
            'request_uri' => $parRequestUri,
        ]);
    }

    public function requestAccessToken(array $params = []): array
    {
        if (! Arr::has($params, 'code')) {
            return [
                'error' => __('mixpost::error.backend.missing_code'),
            ];
        }

        try {
            $response = $this->getAccessTokenResponse($params['code']);

            $this->clearCodeVerifierSession($this->request);

            // Set dynamic provider_id
            $this->values['provider_id'] = $response['did'] ?? $response['sub'] ?? '';

            $dPoPKey = $this->getDPoPKeySession($this->request);
            $this->clearDPoPKeySession($this->request);

            return [
                'access_token' => $response['access_token'],
                'expires_in' => Carbon::now('UTC')->addSeconds($response['expires_in'])->timestamp,
                'refresh_token' => $response['refresh_token'],
                'dpop_key' => $dPoPKey,
            ];
        } catch (AuthenticationException $e) {
            return [
                'error' => __('mixpost::auth.backend.failed'),
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refreshToken(?string $refreshToken = null): SocialProviderResponse
    {
        $payload = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken ?: $this->getRefreshToken(),
            'client_id' => $this->clientId,
            'client_assertion_type' => self::CLIENT_ASSERTION_TYPE,
            'client_assertion' => $this->getClientAssertion(),
        ];

        try {
            $this->setDPoPKeySourceDatabase();

            $response = $this->sendTokenRequest($this->getTokenUrl(), $payload);

            return $this->response(SocialProviderResponseStatus::OK, [
                'access_token' => $response['access_token'],
                'expires_in' => Carbon::now('UTC')->addSeconds($response['expires_in'])->timestamp,
                'refresh_token' => $response['refresh_token'],
            ]);
        } catch (OAuthInvalidGrant $e) {
            return $this->response(SocialProviderResponseStatus::UNAUTHORIZED, [
                'error' => __('mixpost::error.invalid_grant', [], Mixpost::getDefaultLocale()),
            ]);
        } finally {
            $this->resetDPoPKeySource();
        }
    }
}
