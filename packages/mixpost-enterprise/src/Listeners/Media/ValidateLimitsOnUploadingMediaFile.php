<?php

namespace Inovector\MixpostEnterprise\Listeners\Media;

use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\FeatureLimitResources\WorkspaceStorage;

class ValidateLimitsOnUploadingMediaFile
{
    public function handle(object $event): void
    {
        $workspace = WorkspaceManager::current();

        if ($workspace->unlimitedAccess()) {
            return;
        }

        $event->workspace = $workspace;

        app(WorkspaceStorage::class)
            ->limits($workspace->limits)
            ->validator($event)
            ->validate();
    }
}
