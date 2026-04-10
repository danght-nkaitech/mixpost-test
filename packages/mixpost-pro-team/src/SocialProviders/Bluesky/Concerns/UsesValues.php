<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

use Illuminate\Support\Arr;

trait UsesValues
{
    protected function getServer(): string
    {
        return Arr::get($this->values, 'data.server', self::DEFAULT_SERVER);
    }

    protected function getDid(): ?string
    {
        return Arr::get($this->values, 'provider_id');
    }
}
