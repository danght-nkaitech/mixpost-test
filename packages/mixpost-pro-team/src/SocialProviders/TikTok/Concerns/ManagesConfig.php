<?php

namespace Inovector\Mixpost\SocialProviders\TikTok\Concerns;

trait ManagesConfig
{
    public static function getShareType(): string
    {
        return self::service()::getConfiguration('share_type');
    }

    public static function isInboxShareType(): bool
    {
        return self::getShareType() === 'inbox';
    }

    public static function isDirectShareType(): bool
    {
        return self::getShareType() === 'direct';
    }
}
