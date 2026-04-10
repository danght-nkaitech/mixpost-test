<?php

namespace Inovector\Mixpost\Responses;

use Inovector\Mixpost\Data\AIChoiceData;
use Inovector\Mixpost\Enums\AIProviderResponseStatus;

final class AIProviderResponse
{
    public array $context = [];

    public array $choices;

    public array $usage = [];

    public int $retryAfter = 0;

    public bool $rateLimitAboutToBeExceeded = false;

    public function __construct(
        public readonly AIProviderResponseStatus $status
    ) {}

    public static function withStatus(AIProviderResponseStatus $status): self
    {
        return new self($status);
    }

    public function withContext(array $value): self
    {
        $this->context = $value;

        return $this;
    }

    public function withChoices(array $choices): self
    {
        $this->choices = $choices;

        return $this;
    }

    public function withUsage(array $value): self
    {
        $this->usage = $value;

        return $this;
    }

    public function withRetryAfter(int $value): self
    {
        $this->retryAfter = $value;

        return $this;
    }

    public function withRateLimitAboutToBeExceeded(bool $value): self
    {
        $this->rateLimitAboutToBeExceeded = $value;

        return $this;
    }

    public function isOk(): bool
    {
        return $this->status === AIProviderResponseStatus::OK;
    }

    public function hasError(): bool
    {
        return ! $this->isOk();
    }

    public function isUnauthorized(): bool
    {
        return $this->status === AIProviderResponseStatus::UNAUTHORIZED;
    }

    public function hasExceededRateLimit(): bool
    {
        return $this->status === AIProviderResponseStatus::EXCEEDED_RATE_LIMIT;
    }

    public function rateLimitAboutToBeExceeded(): bool
    {
        return $this->rateLimitAboutToBeExceeded;
    }

    public function firstChoice(): ?AIChoiceData
    {
        return $this->choices[0] ?? null;
    }
}
