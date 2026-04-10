<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

trait UsesScopes
{
    protected array $scopes = [
        'atproto',
        'transition:generic',
    ];

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function formatScopes(): string
    {
        return implode(' ', $this->getScopes());
    }
}
