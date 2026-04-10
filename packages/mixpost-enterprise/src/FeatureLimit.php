<?php

namespace Inovector\MixpostEnterprise;

use Illuminate\Support\Arr;
use Inovector\MixpostEnterprise\FeatureLimitResources\AICredits;
use Inovector\MixpostEnterprise\FeatureLimitResources\NumberOfBrandsSocialAccounts;
use Inovector\MixpostEnterprise\FeatureLimitResources\NumberOfSocialAccounts;
use Inovector\MixpostEnterprise\FeatureLimitResources\ScheduledPosts;
use Inovector\MixpostEnterprise\FeatureLimitResources\WorkspaceMembers;
use Inovector\MixpostEnterprise\FeatureLimitResources\WorkspaceStorage;

class FeatureLimit
{
    private static function registered(): array
    {
        return [
            ScheduledPosts::class,
            NumberOfSocialAccounts::class,
            NumberOfBrandsSocialAccounts::class,
            WorkspaceMembers::class,
            WorkspaceStorage::class,
            AICredits::class,
        ];
    }

    public static function list(): array
    {
        return Arr::map(self::registered(), function ($item) {
            return app($item)->render();
        });
    }
}
