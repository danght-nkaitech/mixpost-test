<?php

namespace Inovector\Mixpost\Abstracts;

use Exception;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Configs\AIConfig;
use Inovector\Mixpost\Contracts\AIProvider as AIProviderContract;
use Inovector\Mixpost\Enums\ServiceGroup;
use Inovector\Mixpost\Exceptions\DefaultAIProviderNotSelected;
use Inovector\Mixpost\Facades\ServiceManager;

abstract class AIManager
{
    public function connect(): AIProviderContract
    {
        $default = app(AIConfig::class)->get('provider');

        if (! $default) {
            throw new DefaultAIProviderNotSelected;
        }

        return $this->createConnection($default);
    }

    public function connectProvider(string $name): AIProviderContract
    {
        return $this->createConnection($name);
    }

    public function isAnyServiceActive(): bool
    {
        $services = ServiceManager::services()->group(ServiceGroup::AI)->getNames();

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
        return app(AIConfig::class)->get('provider') ?? null;
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

    private function createConnection(string $name): AIProviderContract
    {
        $provider = Arr::first($this->providers(), function ($provider) use ($name) {
            return $provider::name() === $name;
        });

        if (! $provider) {
            throw new Exception("AI Provider [$name] is not registered.");
        }

        $connection = (new $provider);

        if (! $connection instanceof AIProvider) {
            throw new Exception('The provider must be an instance of Inovector\Mixpost\Abstracts\AIProvider.');
        }

        return $connection;
    }
}
