<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe;

use Inovector\MixpostEnterprise\Abstracts\PaymentPlatform;
use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Concerns\HandleWebhook;
use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Concerns\ManagesSubscriptions;
use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Concerns\SDK;
use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Concerns\StripeForm;

class StripePaymentPlatform extends PaymentPlatform
{
    use HandleWebhook;
    use ManagesSubscriptions;
    use SDK;
    use StripeForm;

    public static function name(): string
    {
        return 'stripe';
    }

    public static function readableName(): string
    {
        return 'Stripe';
    }

    public static function component(): string
    {
        return 'Stripe';
    }

    public function supportResumeSubscription(): bool
    {
        return true;
    }
}
