<?php

namespace Inovector\MixpostEnterprise\Support;

use Illuminate\Validation\ValidationException;
use Inovector\MixpostEnterprise\Enums\FeatureLimitResponseStatus;

class FeatureLimitResponse
{
    public function __construct(
        private readonly FeatureLimitResponseStatus $status,
        private string|array|null $messages = null
    ) {}

    public function status(): FeatureLimitResponseStatus
    {
        return $this->status;
    }

    public function messages(): string|array|null
    {
        return $this->messages;
    }

    public function passes(): bool
    {
        return $this->status === FeatureLimitResponseStatus::PASSES;
    }

    public function fails(): bool
    {
        return ! $this->passes();
    }

    public function withMessages(string|array $messages): static
    {
        $this->messages = $messages;

        return $this;
    }

    public function validate(): void
    {
        if ($this->fails()) {
            throw ValidationException::withMessages([
                'limit' => $this->messages(),
            ]);
        }
    }
}
