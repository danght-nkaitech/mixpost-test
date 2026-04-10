<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\Concerns;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

trait SDK
{
    protected function makeApiCall($method, $uri, array $payload = []): Response
    {
        return Http::withToken($this->credentials['secret_key'])->$method($this->vendorsUrl().$uri, $payload);
    }

    protected function vendorsUrl(): string
    {
        return 'https://api.paystack.co';
    }
}
