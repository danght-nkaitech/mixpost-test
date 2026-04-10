<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\Concerns;

trait StripeForm
{
    public static function formCredentials(): array
    {
        return [
            'key' => '',
            'secret' => '',
            'webhook_secret' => '',
        ];
    }

    public static function formOptions(): array
    {
        return [];
    }

    public static function formRules(): array
    {
        return [
            'credentials.key' => ['required'],
            'credentials.secret' => ['required'],
            'credentials.webhook_secret' => ['required'],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'credentials.key' => 'The Publishable key is required.',
            'credentials.secret' => 'The Secret key is required.',
            'credentials.webhook_secret' => 'The Webhook Signing secret is required.',
        ];
    }
}
