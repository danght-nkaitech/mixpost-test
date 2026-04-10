<?php

namespace Inovector\Mixpost\SocialProviders\Meta\Concerns;

use Inovector\Mixpost\Enums\SocialProviderContentType;

trait ManagesConfig
{
    public static function contentType(): SocialProviderContentType
    {
        return SocialProviderContentType::COMMENTS;
    }

    public static function getApiVersionConfig(): string
    {
        return self::service()::getConfiguration('api_version');
    }
}
