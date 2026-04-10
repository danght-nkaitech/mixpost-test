<?php

namespace Inovector\MixpostEnterprise\Contracts;

use Illuminate\Support\Carbon;
use Inovector\MixpostEnterprise\Models\Subscription;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\SubscriptionInfo;

interface PaymentPlatformSubscription
{
    public function createSubscription(Workspace $workspace, string $planId, array $payload); // TODO: pass parameter  Inovector\MixpostEnterprise\SubscriptionBuilder

    public function subscriptionInfo(Subscription $subscription): SubscriptionInfo;

    public function swapSubscription(Subscription $subscription, string $newPlanId, array $payload): SubscriptionInfo;

    public function cancelSubscription(Subscription $subscription, Carbon $endsAt): SubscriptionInfo;
}
