<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paystack;

use Inovector\MixpostEnterprise\Abstracts\PaymentPlatform;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Concerns\HandleWebhook;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Concerns\ManagesSubscriptions;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Concerns\PaystackForm;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Concerns\SDK;

class PaystackPaymentPlatform extends PaymentPlatform
{
    use HandleWebhook;
    use ManagesSubscriptions;
    use PaystackForm;
    use SDK;

    public static function name(): string
    {
        return 'paystack';
    }

    public static function readableName(): string
    {
        return 'Paystack';
    }

    public static function component(): string
    {
        return 'Paystack';
    }

    public function supportSwapSubscription(): bool
    {
        return false;
    }

    public function supportTrialing(): bool
    {
        return false;
    }

    public function supportCoupon(): bool
    {
        return false;
    }
}
