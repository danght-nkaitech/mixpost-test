<?php

namespace Inovector\Mixpost\Concerns;

use Inovector\Mixpost\Util;

trait UsesCacheKey
{
    protected function resolveCacheKey(string $name, $config = null): string
    {
        if (! $config) {
            return Util::config('cache_prefix').".$name";
        }

        return $config->get('mixpost.cache_prefix').".$name";
    }
}
