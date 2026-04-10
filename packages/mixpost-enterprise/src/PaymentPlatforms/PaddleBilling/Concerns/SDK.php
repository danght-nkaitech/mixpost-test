<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\Concerns;

use Exception;
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
        if (empty($apiKey = $this->credentials['api_key'] ?? null)) {
            throw new Exception('Paddle Billing API key not set.');
        }

        $response = Http::withToken($apiKey)
            ->withHeaders(['Paddle-Version' => 1])
            ->$method($this->vendorsUrl().$uri, $payload);

        if (isset($response['error'])) {
            $message = "Paddle API error '{$response['error']['detail']}' occurred";

            if (isset($response['error']['errors'])) {
                $message .= ' with validation errors ('.json_encode($response['error']['errors']).')';
            }

            throw new PaymentPlatformApiCallException($message);
        }

        return $response;
    }

    protected function vendorsUrl(): string
    {
        $sandboxEnabled = filter_var(
            Arr::get($this->options, 'sandbox', false), FILTER_VALIDATE_BOOLEAN
        );

        return 'https://'.($sandboxEnabled ? 'sandbox-' : '').'api.paddle.com';
    }
}
