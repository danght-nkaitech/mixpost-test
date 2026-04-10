<?php

namespace Inovector\MixpostEnterprise\Actions\Workspace;

use Inovector\Mixpost\Jobs\DeleteWorkspaceDataJob as MainDeleteWorkspaceDataJob;
use Inovector\Mixpost\Models\Workspace;
use Inovector\MixpostEnterprise\Jobs\Subscription\DestroySubscriptionJob;
use Inovector\MixpostEnterprise\Jobs\Workspace\DeleteWorkspaceDataJob as EnterpriseDeleteWorkspaceDataJob;

class DestroyWorkspace
{
    public function __invoke(Workspace $workspace, bool $cancelSubscriptionInQueue = false): void
    {
        if ($subscription = $workspace->subscription()) {
            if (! $cancelSubscriptionInQueue) {
                DestroySubscriptionJob::dispatchSync($subscription);
            }

            if ($cancelSubscriptionInQueue) {
                DestroySubscriptionJob::dispatch($subscription);
            }
        }

        $workspace->delete();

        MainDeleteWorkspaceDataJob::dispatch($workspace->id, $workspace->uuid);
        EnterpriseDeleteWorkspaceDataJob::dispatch($workspace->id, $workspace->uuid);
    }
}
