<?php

namespace Inovector\Mixpost\SocialProviders\Threads\Concerns;

use Illuminate\Support\Carbon;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait ManagesOAuth
{
    public function getAuthUrl(): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => 'threads_basic,threads_delete,threads_content_publish,threads_manage_replies,threads_manage_insights',
            'response_type' => 'code',
            'state' => $this->values['state'],
        ];

        return $this->buildUrlFromBase('https://threads.net/oauth/authorize', $params);
    }

    public function requestAccessToken(array $params = []): array
    {
        $params = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => 'authorization_code',
            'code' => $params['code'],
        ];

        $response = $this->getHttpClient()::post("$this->graphUrl/oauth/access_token", $params)->json();

        if (isset($response['error_message'])) {
            return [
                'error' => $response['error_message'],
            ];
        }

        if (! isset($response['access_token'])) {
            return [
                'error' => __('mixpost::error.invalid_grant'),
            ];
        }

        return $this->requestLongLivedAccessToken($response['access_token']);
    }

    public function requestLongLivedAccessToken(string $shortLivedAccessToken = ''): array
    {
        $params = [
            'grant_type' => 'th_exchange_token',
            'client_secret' => $this->clientSecret,
            'access_token' => $shortLivedAccessToken ?: $this->getAccessToken()['access_token'],
        ];

        $response = $this->getHttpClient()::get("$this->graphUrl/access_token", $params)->json();

        if (isset($response['error_message'])) {
            return [
                'error' => $response['error_message'],
            ];
        }

        return [
            'access_token' => $response['access_token'],
            'expires_in' => Carbon::now('UTC')->addSeconds($response['expires_in'])->timestamp,
        ];
    }

    public function refreshToken(?string $refreshToken = null): SocialProviderResponse
    {
        $params = [
            'grant_type' => 'th_refresh_token',
            'access_token' => $refreshToken ?: $this->getAccessToken()['access_token'],
        ];

        $response = $this->getHttpClient()::get("$this->graphUrl/refresh_access_token", $params);

        return $this->buildResponse($response, function () use ($response) {
            $data = $response->json();

            return [
                'access_token' => $data['access_token'],
                'expires_in' => Carbon::now('UTC')->addSeconds($data['expires_in'])->timestamp,
            ];
        });
    }
}
