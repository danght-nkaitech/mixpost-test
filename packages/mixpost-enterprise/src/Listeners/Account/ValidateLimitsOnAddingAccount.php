<?php

namespace Inovector\MixpostEnterprise\Listeners\Account;

use Inovector\MixpostEnterprise\FeatureLimitResources\NumberOfBrandsSocialAccounts;
use Inovector\MixpostEnterprise\FeatureLimitResources\NumberOfSocialAccounts;

class ValidateLimitsOnAddingAccount
{
    public function handle(object $event): void
    {
        if ($event->workspace->unlimitedAccess()) {
            return;
        }

        app(NumberOfSocialAccounts::class)
            ->limits($event->workspace->limits)
            ->validator($event)
            ->validate();

        app(NumberOfBrandsSocialAccounts::class)
            ->limits($event->workspace->limits)
            ->validator($event)
            ->validate();
    }
}
