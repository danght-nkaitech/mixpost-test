<?php

namespace Inovector\MixpostEnterprise\Actions\Subscription;

use Inovector\MixpostEnterprise\Models\Subscription;

class CancelSubscription
{
    public function __construct(protected readonly Subscription $subscription) {}

    public function cancel(): void
    {
        if ($this->subscription->active()) {
            $this->subscription->cancel();
        }
    }

    public function now(): void
    {
        if ($this->subscription->active()) {
            $this->subscription->cancelNow();
        }
    }
}
