<?php

namespace Inovector\Mixpost\SocialProviders\Google\Concerns;

use Closure;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\Contracts\SocialProviderPostOptions;
use Inovector\Mixpost\Enums\SocialProviderContentType;
use Inovector\Mixpost\SocialProviders\Google\Support\GBPPostOptions;
use Inovector\Mixpost\Support\SocialProviderPostConfigs;

trait GBPManagesConfig
{
    public static function contentType(): SocialProviderContentType
    {
        return SocialProviderContentType::SINGLE;
    }

    public static function postConfigs(Closure $getAccountData): SocialProviderPostConfigs
    {
        return SocialProviderPostConfigs::make()
            ->simultaneousPosting(true)
            ->minTextChar(0)
            ->maxTextChar(1500)
            ->minVideos(0)
            ->maxPhotos(1)
            ->maxVideos(0)
            ->maxGifs(0)
            ->allowMixingMediaTypes(false);
    }

    public static function postOptions(): SocialProviderPostOptions
    {
        return new GBPPostOptions;
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        $data = $accountResource->pivot->data ? json_decode($accountResource->pivot->data, true) : [];

        return Arr::get($data, 'url', '');
    }

    public static function supportAnalytics(): bool
    {
        return false;
    }
}
