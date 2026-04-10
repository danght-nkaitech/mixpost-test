<?php

namespace Inovector\Mixpost\SocialProviders\Threads\Concerns;

use Closure;
use Inovector\Mixpost\Enums\SocialProviderContentType;
use Inovector\Mixpost\Support\SocialProviderPostConfigs;

trait ManagesConfig
{
    public static function contentType(): SocialProviderContentType
    {
        return SocialProviderContentType::THREAD;
    }

    public static function postConfigs(Closure $getAccountData): SocialProviderPostConfigs
    {
        return SocialProviderPostConfigs::make()
            ->simultaneousPosting(true)
            ->minPhotos(0)
            ->minVideos(0)
            ->minGifs(1)
            ->maxTextChar(500)
            ->maxPhotos(20)
            ->maxVideos(20)
            ->maxGifs(20)
            ->allowMixingMediaTypes(true);
    }
}
