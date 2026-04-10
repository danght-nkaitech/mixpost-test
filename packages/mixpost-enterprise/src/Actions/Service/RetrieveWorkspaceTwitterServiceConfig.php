<?php

namespace Inovector\MixpostEnterprise\Actions\Service;

use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Configs\SystemConfig;
use Inovector\MixpostEnterprise\Facades\WorkspaceServiceManager;

class RetrieveWorkspaceTwitterServiceConfig
{
    public function __invoke(?string $key = null): mixed
    {
        if (! WorkspaceManager::current()) {
            return null;
        }

        if (! app(SystemConfig::class)->allowWorkspaceTwitterService()) {
            return null;
        }

        return WorkspaceServiceManager::get('twitter', $key);
    }
}
