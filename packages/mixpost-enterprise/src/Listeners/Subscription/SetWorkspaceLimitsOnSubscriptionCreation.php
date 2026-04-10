<?php

namespace Inovector\MixpostEnterprise\Listeners\Subscription;

use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionCreated;

class SetWorkspaceLimitsOnSubscriptionCreation
{
    public function handle(SubscriptionCreated $event): void
    {
        if (! $event->subscription->plan()) {
            return;
        }

        $event->subscription->workspace->saveAsAccessStatusSubscription();
        $event->subscription->workspace->removeGenericSubscription();
        $event->subscription->workspace->saveLimits($event->subscription->plan()->limits);
    }
}
