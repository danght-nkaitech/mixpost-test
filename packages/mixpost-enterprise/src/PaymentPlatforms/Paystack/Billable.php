<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paystack;

use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Concerns\ManagesCustomer;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Concerns\SDK;

class Billable
{
    use ManagesCustomer;
    use SDK;

    public Workspace $workspace;

    public function __construct(public readonly array $credentials, Workspace $workspace)
    {
        $this->workspace = $workspace;
    }
}
