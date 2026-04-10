<?php

namespace Inovector\MixpostEnterprise\Listeners\Subscription;

use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionUpdated;

class UpdateWorkspaceLimitsOnSubscriptionUpdate
{
    public function handle(SubscriptionUpdated $event): void
    {
        if (! $event->subscription->workspace) {
            return;
        }

        if (! $event->subscription->plan()) {
            return;
        }

        $event->subscription->workspace->saveAsAccessStatusSubscription();
        $event->subscription->workspace->removeGenericSubscription();
        $event->subscription->workspace->saveLimits($event->subscription->plan()->limits);
    }
}
