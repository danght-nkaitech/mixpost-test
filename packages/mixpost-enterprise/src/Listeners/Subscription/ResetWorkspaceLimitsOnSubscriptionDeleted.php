<?php

namespace Inovector\MixpostEnterprise\Listeners\Subscription;

use Illuminate\Support\Facades\DB;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionDeleted;

class ResetWorkspaceLimitsOnSubscriptionDeleted
{
    public function handle(SubscriptionDeleted $event)
    {
        DB::transaction(function () use ($event) {
            $event->workspace->saveAsAccessStatusSubscription();
            $event->workspace->removeGenericSubscription();
            $event->workspace->removePaymentMethod();
            $event->workspace->saveLimits([]);
        });
    }
}
