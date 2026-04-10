<?php

namespace Inovector\Mixpost;

class Features
{
    public static function isTwoFactorAuthEnabled(): bool
    {
        return self::enabled('two_factor_auth');
    }

    public static function isForgotPasswordEnabled(): bool
    {
        return self::enabled('forgot_password');
    }

    public static function isApiAccessTokenEnabled(): bool
    {
        return self::enabled('api_access_tokens');
    }

    public static function isAutoSubscribePostActivitiesEnabled(): bool
    {
        return self::enabled('auto_subscribe_post_activities');
    }

    public static function isDeleteAccountEnabled()
    {
        $config = Mixpost::getEnterpriseConfig()['onboarding'] ?? null;

        if (! $config) {
            return false;
        }

        return app($config)->get('allow_account_deletion');
    }

    private static function enabled(string $feature): bool
    {
        return Util::config("features.$feature", false);
    }
}
