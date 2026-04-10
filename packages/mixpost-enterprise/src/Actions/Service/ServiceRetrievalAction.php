<?php

namespace Inovector\MixpostEnterprise\Actions\Service;

/**
 * This action overrides `Inovector\Mixpost\Facades\ServiceManager::get()` method and
 * retrieve the configuration of a service for a workspace.
 */
class ServiceRetrievalAction
{
    public function __invoke(string $name, ?string $key = null)
    {
        return match ($name) {
            'twitter' => (new RetrieveWorkspaceTwitterServiceConfig)($key),
            default => null,
        };
    }
}
