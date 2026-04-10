<?php

namespace Inovector\Mixpost\SocialProviders\Google\Concerns;

use Illuminate\Support\Arr;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait GBPManagesAccount
{
    public function getAccount(): SocialProviderResponse
    {
        $response = $this->getEntities();

        if ($response->hasError()) {
            return $response;
        }

        $filter = array_values(Arr::where($response->context(), function ($entity) {
            return $entity['id'] === $this->values['provider_id'];
        }));

        return new SocialProviderResponse(SocialProviderResponseStatus::OK, $filter[0] ?? []);
    }

    public function getEntities(): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        $profileAccounts = $this->getProfileAccounts();

        if ($profileAccounts->hasError()) {
            return $profileAccounts;
        }

        $firstAccount = Arr::first($profileAccounts->context());

        $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->get("https://mybusinessbusinessinformation.googleapis.com/v1/{$firstAccount['name']}/locations", [
                'readMask' => 'title,name',
            ]);

        return $this->buildResponse($response, function () use ($response, $firstAccount) {
            return $response->collect('locations')->map(function ($item) use ($firstAccount) {
                return [
                    'id' => $item['name'],
                    'name' => $item['title'],
                    'username' => '',
                    'image' => '',
                    'data' => ['account' => $firstAccount],
                ];
            })->toArray();
        });
    }

    public function getProfileAccounts(): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->get('https://mybusinessaccountmanagement.googleapis.com/v1/accounts');

        return $this->buildResponse($response, function () use ($response) {
            return $response->collect('accounts')->map(function ($item) {
                return [
                    'name' => $item['name'],
                    'accountName' => $item['accountName'],
                ];
            })->toArray();
        });
    }
}
