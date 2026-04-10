<?php

namespace Inovector\Mixpost\SocialProviders\Mastodon\Concerns;

use Illuminate\Support\Arr;
use Inovector\Mixpost\Enums\SocialProviderContentType;

trait ManagesConfig
{
    public static function contentType(): SocialProviderContentType
    {
        return SocialProviderContentType::THREAD;
    }

    public function getServerMaxChars(): ?int
    {
        $instanceData = $this->getServerInstanceData();

        return Arr::get($instanceData, 'configuration.statuses.max_characters');
    }
}
