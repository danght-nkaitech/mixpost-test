<?php

namespace Inovector\Mixpost\SocialProviders\Twitter\Concerns;

use Inovector\Mixpost\Enums\SocialProviderContentType;

trait ManagesConfig
{
    public static function contentType(): SocialProviderContentType
    {
        return SocialProviderContentType::THREAD;
    }

    public static function getTier(): string
    {
        return self::service()::getConfiguration('tier');
    }
}
