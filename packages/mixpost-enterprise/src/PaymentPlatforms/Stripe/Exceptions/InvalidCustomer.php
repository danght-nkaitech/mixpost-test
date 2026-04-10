<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Exceptions;

use Exception;
use Inovector\MixpostEnterprise\Models\Workspace;

class InvalidCustomer extends Exception
{
    public static function notYetCreated(Workspace $workspace): static
    {
        return new static($workspace->name.' is not a Stripe customer yet. See the createCustomer method.');
    }
}
