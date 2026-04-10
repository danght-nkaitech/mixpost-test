<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Concerns;

use Stripe\StripeClient;

trait SDK
{
    protected function client(): StripeClient
    {
        return new StripeClient([
            'api_key' => $this->credentials['secret'],
            'stripe_version' => '2023-08-16',
        ]);
    }
}
