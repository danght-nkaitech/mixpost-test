<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

trait UsesAuthServer
{
    /**
     * @link  https://bsky.social/.well-known/oauth-authorization-server
     */
    protected function authServerMeta(?string $key = null, string $default = ''): array|string
    {
        $serverMeta = $this->getHttpClient()::baseUrl($this->values['data']['server'])
            ->get('/.well-known/oauth-authorization-server')
            ->json();

        if (empty($key)) {
            return $serverMeta;
        }

        return data_get($serverMeta, $key, $default);
    }
}
