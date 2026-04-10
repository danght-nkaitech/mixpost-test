<?php

namespace Inovector\Mixpost\SocialProviders\Linkedin\Concerns;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait ManagesRateLimit
{
    /**
     * @param  $response  Response
     */
    public function buildResponse($response, ?Closure $okResult = null): SocialProviderResponse
    {
        $usage = $this->getRateLimitUsage($response);
        $data = Arr::wrap($response->json());

        if ($response->status() === 429 || $usage['exceeded']) {
            return $this->response(
                SocialProviderResponseStatus::EXCEEDED_RATE_LIMIT,
                $this->rateLimitExceedContext($usage['retry_after']),
                false,
                $usage['retry_after'],
                true
            );
        }

        if ($response->status() === 401) {
            return $this->response(
                SocialProviderResponseStatus::UNAUTHORIZED,
                ['access_token_expired']
            );
        }

        if (in_array($response->status(), [200, 201, 204])) {
            return $this->response(SocialProviderResponseStatus::OK, $okResult ? $okResult($data) : $data);
        }

        return $this->response(
            SocialProviderResponseStatus::ERROR,
            $data
        );
    }

    /**
     * Rate limit
     *
     * @see https://learn.microsoft.com/en-us/linkedin/shared/api-guide/concepts/rate-limits
     */
    public function getRateLimitUsage(Response $response): array
    {
        $now = Carbon::now()->utc();
        $nextMidnight = Carbon::tomorrow('UTC');
        $retryAfter = (int) $now->diffInSeconds($nextMidnight);

        return [
            'retry_after' => $retryAfter,
            'exceeded' => Arr::get($response->json(), 'elements.0.organizationalTarget!.status') === 429,
        ];
    }
}
