<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paddle\Concerns;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Inovector\MixpostEnterprise\Exceptions\PaymentPlatformApiCallException;

trait SDK
{
    /**
     * @throws PaymentPlatformApiCallException
     */
    protected function makeApiCall($method, $uri, array $payload = []): Response
    {
        $response = Http::$method($this->vendorsUrl().'/api/2.0'.$uri, array_merge($this->credentials, $payload));

        if ($response['success'] === false) {
            throw new PaymentPlatformApiCallException($response['error']['message'], $response['error']['code']);
        }

        return $response;
    }

    protected function vendorsUrl(): string
    {
        $sandboxEnabled = filter_var(
            Arr::get($this->options, 'sandbox', false), FILTER_VALIDATE_BOOLEAN
        );

        return 'https://'.($sandboxEnabled ? 'sandbox-' : '').'vendors.paddle.com';
    }
}
