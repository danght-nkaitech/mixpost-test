<?php

namespace Inovector\Mixpost\SocialProviders\Threads\Concerns;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait UsesResponseBuilder
{
    /**
     * @param  $response  Response
     */
    public function buildResponse($response, ?Closure $okResult = null): SocialProviderResponse
    {
        $businessUsage = $this->getBusinessUsage($response->headers());

        $rateLimitAboutToBeExceeded = false;
        $retryAfter = 0;

        // Check Business usage
        if ($businessUsage && ($businessUsage['call_count'] > 90 || $businessUsage['total_time'] > 90 || $businessUsage['total_cputime'] > 90)) {
            $rateLimitAboutToBeExceeded = true;
            $retryAfter = $businessUsage['retry_after'] === 0 ? 60 * 60 : $businessUsage['retry_after'];
        }

        if (! Arr::has($response->json(), 'error')) {
            return $this->response(
                SocialProviderResponseStatus::OK,
                $okResult ? $okResult() : $response->json(),
                $rateLimitAboutToBeExceeded,
                $retryAfter,
            );
        }

        $rateLimitError = in_array($response->json()['error']['code'], [4, 613, 80000, 80004, 80003, 80002, 80005, 80006, 32, 80001, 17, 80008, 80014, 80009]);

        if ($rateLimitError) {
            return $this->response(
                SocialProviderResponseStatus::EXCEEDED_RATE_LIMIT,
                $this->rateLimitExceedContext($retryAfter, $response->json()['error']['message']),
                $rateLimitAboutToBeExceeded,
                $retryAfter,
            );
        }

        if ($response->json()['error']['code'] === 190) {
            return $this->response(
                SocialProviderResponseStatus::UNAUTHORIZED,
                ['access_token_expired']
            );
        }

        return $this->response(
            SocialProviderResponseStatus::ERROR,
            $response->json(),
            $rateLimitAboutToBeExceeded,
            $retryAfter,
        );
    }

    /**
     * Business Use Case Rate Limits
     *
     * @see https://developers.facebook.com/docs/threads/overview#rate-limiting
     */
    public function getBusinessUsage(array $headers): ?array
    {
        if (! Arr::has($this->values, 'provider_id')) {
            return null;
        }

        $usage = Arr::get($headers, 'x-business-use-case-usage.0', []);

        if (! is_array($usage)) {
            $usage = json_decode($usage, true);
        }

        $usage = Arr::get($usage, "{$this->values['provider_id']}.0");

        return [
            'business' => $usage,
            'type' => Arr::get($usage, '0.type'),
            'call_count' => Arr::get($usage, '0.call_count', 0),
            'total_cputime' => Arr::get($usage, '0.total_cputime', 0),
            'total_time' => Arr::get($usage, '0.total_time', 0),
            'retry_after' => Arr::get($usage, '0.estimated_time_to_regain_access', 0) * 60,
        ];
    }
}
