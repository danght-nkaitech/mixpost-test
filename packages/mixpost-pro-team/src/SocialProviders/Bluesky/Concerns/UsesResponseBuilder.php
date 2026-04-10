<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait UsesResponseBuilder
{
    /**
     * @param  $response  Response
     */
    public function buildResponse($response, ?Closure $okResult = null): SocialProviderResponse
    {
        $usage = $this->getRateLimitUsage($response->headers());

        $rateLimitAboutToBeExceeded = $usage['remaining'] < 5;
        $retryAfter = $rateLimitAboutToBeExceeded ? 5 * 60 : $usage['retry_after'];

        if ($response->tooManyRequests()) {
            return $this->response(
                SocialProviderResponseStatus::EXCEEDED_RATE_LIMIT,
                $this->rateLimitExceedContext($usage['retry_after']),
                $rateLimitAboutToBeExceeded,
                $retryAfter
            );
        }

        if ($response->unauthorized()) {
            return $this->response(
                SocialProviderResponseStatus::UNAUTHORIZED,
                ['access_token_expired'],
                $rateLimitAboutToBeExceeded,
                $retryAfter,
            );
        }

        if ($response->badRequest()) {
            return $this->response(
                SocialProviderResponseStatus::ERROR,
                Arr::wrap($response->json()),
                $rateLimitAboutToBeExceeded,
                $retryAfter,
            );
        }

        if ($response->json('error')) {
            return $this->response(
                SocialProviderResponseStatus::ERROR,
                Arr::wrap($response->json()),
                $rateLimitAboutToBeExceeded,
                $retryAfter,
            );
        }

        return $this->response(
            SocialProviderResponseStatus::OK,
            $okResult ? $okResult($response->json()) : $response->json(),
            $rateLimitAboutToBeExceeded,
            $retryAfter,
        );
    }

    /**
     * Rate limit
     *
     * @see https://docs.bsky.app/docs/advanced-guides/rate-limits
     */
    public function getRateLimitUsage(array $headers): array
    {
        $headers = array_change_key_case($headers, CASE_LOWER);
        $timestampToRegainAccess = Carbon::parse((int) Arr::get($headers, 'ratelimit-reset.0'));

        return [
            'limit' => (int) Arr::get($headers, 'ratelimit-limit.0'),
            'remaining' => (int) Arr::get($headers, 'ratelimit-remaining.0'),
            'retry_after' => (int) Carbon::now('UTC')->diffInSeconds($timestampToRegainAccess),
        ];
    }
}
