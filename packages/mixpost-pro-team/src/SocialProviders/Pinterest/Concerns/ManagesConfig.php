<?php

namespace Inovector\Mixpost\SocialProviders\Pinterest\Concerns;

trait ManagesConfig
{
    public static function getEnvironment(): string
    {
        return self::service()::getConfiguration('environment');
    }

    public static function isSandbox(): bool
    {
        return self::getEnvironment() === 'sandbox';
    }
}
