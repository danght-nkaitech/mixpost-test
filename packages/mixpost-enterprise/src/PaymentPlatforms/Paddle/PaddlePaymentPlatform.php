<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paddle;

use Inovector\MixpostEnterprise\Abstracts\PaymentPlatform;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paddle\Concerns\HandleWebhook;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paddle\Concerns\ManagesSubscriptions;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paddle\Concerns\PaddleForm;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paddle\Concerns\SDK;

class PaddlePaymentPlatform extends PaymentPlatform
{
    use HandleWebhook;
    use ManagesSubscriptions;
    use PaddleForm;
    use SDK;

    public static function name(): string
    {
        return 'paddle';
    }

    public static function readableName(): string
    {
        return 'Paddle Classic (Deprecated)';
    }

    public static function component(): string
    {
        return 'Paddle';
    }
}
