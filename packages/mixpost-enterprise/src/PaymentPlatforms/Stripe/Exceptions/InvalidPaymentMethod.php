<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Exceptions;

use Exception;
use Inovector\MixpostEnterprise\Models\Workspace;
use Stripe\PaymentMethod as StripePaymentMethod;

class InvalidPaymentMethod extends Exception
{
    public static function invalidWorkspace(StripePaymentMethod $paymentMethod, Workspace $workspace): static
    {
        return new static(
            "The payment method `{$paymentMethod->id}` does not belong to this workspace `$workspace->stripe_id`."
        );
    }
}
