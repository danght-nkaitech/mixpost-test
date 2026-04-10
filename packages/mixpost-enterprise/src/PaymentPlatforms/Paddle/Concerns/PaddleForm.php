<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paddle\Concerns;

use Illuminate\Validation\Rule;

trait PaddleForm
{
    public static function formCredentials(): array
    {
        return [
            'vendor_id' => '',
            'vendor_auth_code' => '',
            'public_key' => '',
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
            'credentials.vendor_id' => ['required'],
            'credentials.vendor_auth_code' => ['required'],
            'credentials.public_key' => ['required'],
            'options.sandbox' => ['required', Rule::in(['true', 'false', true, false])],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'credentials.vendor_id' => 'The Vendor ID is required.',
            'credentials.vendor_auth_code' => 'The Vendor Auth Code is required.',
            'credentials.public_key' => 'The Public Key is required.',
            'options.sandbox' => 'The Sandbox is required.',
        ];
    }
}
