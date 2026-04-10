<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inovector\Mixpost\Contracts\AccountResource;

trait HasExternalUrls
{
    public static function externalAccountUrl(AccountResource $accountResource): string
    {
        $domain = self::getClientDomain($accountResource);

        return "$domain/profile/$accountResource->username";
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        $domain = self::getClientDomain($accountResource);

        $data = json_decode($accountResource->pivot->data ?? '{}', true);

        $uri = Arr::get($data, 'uri', '');
        $slug = Str::afterLast($uri, '/');

        if (! $slug) {
            return '';
        }

        return "$domain/profile/$accountResource->username/post/$slug";
    }

    private static function getClientDomain(AccountResource $accountResource): string
    {
        $server = Arr::get($accountResource->values(), 'data.server', self::DEFAULT_SERVER); // Workaround to get server. `getServer()` is not a static method.

        // Return the official domain if the server is the official Bluesky.
        if ($server === self::DEFAULT_SERVER) {
            return 'https://bsky.app';
        }

        return $server;
    }
}
