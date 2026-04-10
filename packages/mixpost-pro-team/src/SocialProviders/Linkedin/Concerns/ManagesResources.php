<?php

namespace Inovector\Mixpost\SocialProviders\Linkedin\Concerns;

use Illuminate\Support\Arr;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait ManagesResources
{
    use ManagesPost;

    public function getAccount(): SocialProviderResponse
    {
        if ($this->hasRefreshToken() && $this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        if (self::hasSignInOpenIdShareProduct()) {
            $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
                ->withHeaders($this->httpHeaders())
                ->get("$this->apiUrl/$this->apiVersion/userinfo");

            return $this->buildResponse($response, function () use ($response) {
                $data = $response->json();

                return [
                    'id' => $data['sub'],
                    'name' => $data['name'],
                    'username' => '',
                    'image' => $data['picture'] ?? '',
                ];
            });
        }

        $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->withHeaders($this->httpHeaders())
            ->get("$this->apiUrl/$this->apiVersion/me", [
                'projection' => '(id,localizedFirstName,localizedLastName,vanityName,profilePicture(displayImage~:playableStreams))',
            ]);

        return $this->buildResponse($response, function () use ($response) {
            $data = $response->json();

            return [
                'id' => $data['id'],
                'name' => "{$data['localizedFirstName']} {$data['localizedLastName']}",
                'username' => $data['vanityName'] ?? '',
                'image' => Arr::get($data, 'profilePicture.displayImage~.elements.0.identifiers.0.identifier'),
            ];
        });
    }

    private function author(): string
    {
        return $this->values['provider'] === 'linkedin_page' ? 'organization' : 'person';
    }
}
