<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\Concerns;

use Illuminate\Validation\Rule;

trait PaddleForm
{
    public static function formCredentials(): array
    {
        return [
            'api_key' => '',
            'client_side_token' => '',
            'webhook_secret' => '',
        ];
    }

    public static function formOptions(): array
    {
        return [
            'sandbox' => true,
        ];
    }

    public static function formRules(): array
    {
        return [
            'credentials.api_key' => ['required'],
            'credentials.client_side_token' => ['required'],
            'credentials.webhook_secret' => ['required'],
            'options.sandbox' => ['required', Rule::in(['true', 'false', true, false])],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'credentials.api_key' => 'The API Key is required.',
            'credentials.client_side_token' => 'The Client Side Token is required.',
            'credentials.webhook_secret' => 'The Webhook Secret is required.',
            'options.sandbox' => 'The Sandbox is required.',
        ];
    }
}
