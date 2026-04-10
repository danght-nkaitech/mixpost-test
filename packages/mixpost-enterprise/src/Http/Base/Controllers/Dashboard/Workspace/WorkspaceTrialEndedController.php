<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Facades\WorkspaceManager;

class WorkspaceTrialEndedController
{
    public function __invoke(): Response
    {
        $workspace = WorkspaceManager::current();

        if (! ($workspace->hasGenericSubscription() && ! $workspace->genericSubscriptionFree() && $workspace->hasExpiredGenericTrial())) {
            abort(404);
        }

        return Inertia::render('Dashboard/Workspace/TrialEnded');
    }
}
