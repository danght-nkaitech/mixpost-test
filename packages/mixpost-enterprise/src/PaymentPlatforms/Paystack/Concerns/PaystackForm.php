<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Concerns;

trait PaystackForm
{
    public static function formCredentials(): array
    {
        return [
            'secret_key' => '',
            'public_key' => '',
        ];
    }

    public static function formOptions(): array
    {
        return [
            //
        ];
    }

    public static function formRules(): array
    {
        return [
            'credentials.secret_key' => ['required'],
            'credentials.public_key' => ['required'],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'credentials.secret_key' => 'The Secret Key is required.',
            'credentials.public_key' => 'The Public Key is required.',
        ];
    }
}
