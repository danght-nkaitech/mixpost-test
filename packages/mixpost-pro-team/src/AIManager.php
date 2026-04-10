<?php

namespace Inovector\Mixpost;

use Inovector\Mixpost\Abstracts\AIManager as AIManagerAbstract;
use Inovector\Mixpost\AIProviders\OpenAI\OpenAIProvider;

class AIManager extends AIManagerAbstract
{
    protected array $cacheProviders = [];

    public function registeredProviders(): array
    {
        return [
            OpenAIProvider::class,
        ];
    }

    public function providers(): array
    {
        if (! empty($this->cacheProviders)) {
            return $this->cacheProviders;
        }

        return $this->cacheProviders = $this->registeredProviders();
    }
}
