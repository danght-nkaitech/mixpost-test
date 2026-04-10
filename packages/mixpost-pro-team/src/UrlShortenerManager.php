<?php

namespace Inovector\Mixpost;

use Inovector\Mixpost\Abstracts\UrlShortenerManager as UrlShortenerManagerAbstract;
use Inovector\Mixpost\UrlShortenerProviders\BitlyProvider;
use Inovector\Mixpost\UrlShortenerProviders\ShlinkProvider;
use Inovector\Mixpost\UrlShortenerProviders\YourlsProvider;

class UrlShortenerManager extends UrlShortenerManagerAbstract
{
    protected array $cacheProviders = [];

    public function registeredProviders(): array
    {
        return [
            YourlsProvider::class,
            ShlinkProvider::class,
            BitlyProvider::class,
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
