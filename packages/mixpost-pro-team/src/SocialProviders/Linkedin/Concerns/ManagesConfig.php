<?php

namespace Inovector\Mixpost\SocialProviders\Linkedin\Concerns;

use Inovector\Mixpost\Enums\SocialProviderContentType;

trait ManagesConfig
{
    public static function contentType(): SocialProviderContentType
    {
        if (self::hasCommunityManagementProduct()) {
            return SocialProviderContentType::COMMENTS;
        }

        return SocialProviderContentType::SINGLE;
    }

    public static function getProduct(): string
    {
        return self::service()::getConfiguration('product');
    }

    public static function hasCommunityManagementProduct(): bool
    {
        return self::getProduct() === 'community_management';
    }

    public static function hasSignInOpenIdShareProduct(): bool
    {
        return self::getProduct() === 'sign_open_id_share';
    }
}
