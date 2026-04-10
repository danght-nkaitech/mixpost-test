<?php

namespace Inovector\Mixpost\SocialProviders\Google\Concerns;

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
        if (in_array($response->status(), [200, 201, 204])) {
            return $this->response(SocialProviderResponseStatus::OK, $okResult ? $okResult(Arr::wrap($response->json())) : Arr::wrap($response->json()));
        }

        if ($response->status() === 401) {
            return $this->response(SocialProviderResponseStatus::UNAUTHORIZED, Arr::wrap($response->json()));
        }

        if ($response->status() === 403) {
            $now = Carbon::now('UTC');
            $nextMidnight = Carbon::tomorrow('UTC');
            $retryAfter = (int) $now->diffInSeconds($nextMidnight);

            return $this->response(
                SocialProviderResponseStatus::EXCEEDED_RATE_LIMIT,
                $this->rateLimitExceedContext($retryAfter, 'The daily quota has been exceeded.'),
                false,
                $retryAfter,
                true
            );
        }

        return $this->response(SocialProviderResponseStatus::ERROR, Arr::wrap($response->json()));
    }

    /**
     * YouTube Data API (v3) - Quota Calculator
     *
     * @see https://developers.google.com/youtube/v3/determine_quota_cost
     */
    public function getQuotaUsage(array $headers): ?array
    {
        return null;
    }
}
