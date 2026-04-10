<?php

namespace Inovector\Mixpost\Abstracts;

use Exception;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Configs\GeneralConfig;
use Inovector\Mixpost\Contracts\UrlShortenerProvider as UrlShortenerContract;
use Inovector\Mixpost\Enums\ServiceGroup;
use Inovector\Mixpost\Facades\ServiceManager;

abstract class UrlShortenerManager
{
    public function connect(): UrlShortenerContract
    {
        $default = app(GeneralConfig::class)->get('url_shortener_provider');

        if ($default === 'disabled') {
            throw new Exception('URL Shortener disabled. Add an URL Shortener service and then configure it on General settings page.');
        }

        return $this->createConnection($default);
    }

    public function connectProvider(string $name): UrlShortenerContract
    {
        return $this->createConnection($name);
    }

    public function isAnyServiceActive(): bool
    {
        $services = ServiceManager::services()->group(ServiceGroup::URL_SHORTENER)->getNames();

        return in_array(
            true,
            ServiceManager::isActive($services)
        );
    }

    public function isReadyToUse(): bool
    {
        $defaultProvider = $this->getDefaultProviderName();

        if (! $defaultProvider) {
            return false;
        }

        return ServiceManager::isActive($defaultProvider);
    }

    public function getDefaultProviderName(): ?string
    {
        return app(GeneralConfig::class)->get('url_shortener_provider') ?? null;
    }

    public function getProviderSelectionOptions(): array
    {
        return array_reduce($this->providers(), function ($array, $provider) {
            $array[$provider::name()] = $provider::nameLocalized();

            return $array;
        }, []);
    }

    public function getProviderSelectionOptionKeys(): array
    {
        return array_keys($this->getProviderSelectionOptions());
    }

    private function createConnection(string $name): UrlShortenerContract
    {
        $provider = Arr::first($this->providers(), function ($provider) use ($name) {
            return $provider::name() === $name;
        });

        if (! $provider) {
            throw new Exception("URL Shortener Provider [$name] is not registered.");
        }

        $connection = (new $provider);

        if (! $connection instanceof UrlShortenerContract) {
            throw new Exception('The provider must be an instance of Inovector\Mixpost\Contracts\UrlShortenerProvider.');
        }

        return $connection;
    }
}
