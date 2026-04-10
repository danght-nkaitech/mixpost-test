<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paystack;

use Illuminate\Support\Carbon;
use Inovector\MixpostEnterprise\Enums\SubscriptionStatus;

class Util
{
    public static function mapStatus(string $paystackStatus): SubscriptionStatus
    {
        return match ($paystackStatus) {
            'active' => SubscriptionStatus::ACTIVE,
            'attention' => SubscriptionStatus::PAST_DUE,
            'non-renewing', 'cancelled' => SubscriptionStatus::CANCELED,
            default => SubscriptionStatus::INCOMPLETE,
        };
    }

    public static function createDateTime(string $value): Carbon
    {
        return Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $value, 'UTC');
    }
}
