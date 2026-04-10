<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe;

use Inovector\MixpostEnterprise\Enums\SubscriptionStatus;
use Stripe\Subscription as StripeSubscription;

class Util
{
    public static function mapStatus(string $stripeStatus): SubscriptionStatus
    {
        return match ($stripeStatus) {
            StripeSubscription::STATUS_ACTIVE => SubscriptionStatus::ACTIVE,
            StripeSubscription::STATUS_TRIALING => SubscriptionStatus::TRIALING,
            StripeSubscription::STATUS_PAST_DUE => SubscriptionStatus::PAST_DUE,
            StripeSubscription::STATUS_PAUSED => SubscriptionStatus::PAUSED,
            StripeSubscription::STATUS_CANCELED => SubscriptionStatus::CANCELED,
            default => SubscriptionStatus::INCOMPLETE,
        };
    }
}
