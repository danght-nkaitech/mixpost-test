<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

use Illuminate\Support\Arr;

trait UsesAccessToken
{
    protected function getToken(): string
    {
        return Arr::get($this->getAccessToken(), 'access_token');
    }

    protected function getRefreshToken(): string
    {
        return Arr::get($this->getAccessToken(), 'refresh_token');
    }

    protected function getDPoPKey(): string
    {
        return Arr::get($this->getAccessToken(), 'dpop_key');
    }
}
