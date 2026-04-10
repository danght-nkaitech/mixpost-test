<?php

namespace Inovector\MixpostEnterprise\Listeners\Subscription;

use Inovector\MixpostEnterprise\Enums\SubscriptionStatus;

class SetSubscriptionPastDue
{
    public function handle(object $event): void
    {
        $event->subscription->update(['status' => SubscriptionStatus::PAST_DUE->value]);
    }
}
