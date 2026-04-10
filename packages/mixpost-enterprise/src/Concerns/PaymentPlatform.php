<?php

namespace Inovector\MixpostEnterprise\Concerns;

use Inovector\MixpostEnterprise\Contracts\PaymentPlatform as PaymentPlatformContract;
use Inovector\MixpostEnterprise\PaymentPlatform as PaymentPlatformCore;

trait PaymentPlatform
{
    public function activePlatform(): PaymentPlatformContract
    {
        return PaymentPlatformCore::activePlatformInstance();
    }
}
