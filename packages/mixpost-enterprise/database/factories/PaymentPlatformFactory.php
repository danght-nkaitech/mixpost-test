<?php

namespace Inovector\MixpostEnterprise\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\MixpostEnterprise\Models\PaymentPlatform;

class PaymentPlatformFactory extends Factory
{
    protected $model = PaymentPlatform::class;

    public function definition()
    {
        return [
            'name' => 'paddle',
            'credentials' => [
                'vendor_id' => '123456',
                'vendor_auth_code' => '1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef',
                'public_key' => '1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef',
            ],
            'options' => [
                'sandbox' => true,
            ],
            'enabled' => true,
        ];
    }
}
