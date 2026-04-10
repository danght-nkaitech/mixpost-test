<?php

namespace Inovector\MixpostEnterprise\Listeners\Workspace;

use Inovector\MixpostEnterprise\FeatureLimitResources\WorkspaceMembers;

class ValidateLimitsOnInvitingMember
{
    public function handle(object $event): void
    {
        if ($event->workspace->unlimitedAccess()) {
            return;
        }

        app(WorkspaceMembers::class)
            ->limits($event->workspace->limits)
            ->validator($event)
            ->validate();
    }
}
