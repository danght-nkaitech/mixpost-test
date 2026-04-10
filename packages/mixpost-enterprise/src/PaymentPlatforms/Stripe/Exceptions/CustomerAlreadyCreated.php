<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Exceptions;

use Exception;
use Inovector\MixpostEnterprise\Models\Workspace;

class CustomerAlreadyCreated extends Exception
{
    public static function exists(Workspace $workspace): static
    {
        return new static($workspace->name." is already a Stripe customer with ID {$workspace->stripe_id}.");
    }
}
