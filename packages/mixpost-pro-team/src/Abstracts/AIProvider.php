<?php

namespace Inovector\Mixpost\Abstracts;

use Illuminate\Support\Str;
use Inovector\Mixpost\Contracts\AIProvider as AIProviderContract;
use Inovector\Mixpost\Exceptions\AIProviderInactive;

abstract class AIProvider implements AIProviderContract
{
    public function __construct()
    {
        if (! $this->isServiceActive()) {
            throw new AIProviderInactive($this->service()::nameLocalized());
        }
    }

    /**
     * Unique name of the provider.
     * Should be lowercase and snake cased.
     */
    public static function name(): string
    {
        $className = basename(str_replace('\\', '/', static::class));

        return Str::of($className)->replace('Provider', '')->snake();
    }

    /**
     * Localized name of the provider.
     * Friendly name for the user interface.
     */
    public static function nameLocalized(): string
    {
        $className = basename(str_replace('\\', '/', static::class));

        return Str::of($className)->replace('Provider', '');
    }

    public function getServiceConfiguration(?string $key = null)
    {
        return $this->service()::getConfiguration($key);
    }

    public function isServiceActive()
    {
        return $this->service()::isActive();
    }
}
