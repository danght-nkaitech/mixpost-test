<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling;

use Inovector\MixpostEnterprise\Abstracts\PaymentPlatform;
use Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\Concerns\HandleWebhook;
use Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\Concerns\ManagesCustomer;
use Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\Concerns\ManagesReceipts;
use Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\Concerns\ManagesSubscriptions;
use Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\Concerns\PaddleForm;
use Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\Concerns\SDK;

class PaddleBillingPaymentPlatform extends PaymentPlatform
{
    use HandleWebhook;
    use ManagesCustomer;
    use ManagesReceipts;
    use ManagesSubscriptions;
    use PaddleForm;
    use SDK;

    public static function name(): string
    {
        return 'paddle_billing';
    }

    public static function readableName(): string
    {
        return 'Paddle Billing';
    }

    public static function component(): string
    {
        return 'PaddleBilling';
    }

    public function supportResumeSubscription(): bool
    {
        return true;
    }

    public function supportReceiptUrl(): bool
    {
        return true;
    }
}
