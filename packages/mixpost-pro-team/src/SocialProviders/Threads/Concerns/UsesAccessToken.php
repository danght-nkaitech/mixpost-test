<?php

namespace Inovector\Mixpost\SocialProviders\Threads\Concerns;

trait UsesAccessToken
{
    protected function accessToken(): string
    {
        return $this->getAccessToken()['access_token'];
    }
}
