<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Facades\WorkspaceManager;

class WorkspaceLockedController
{
    public function __invoke(): Response
    {
        $workspace = WorkspaceManager::current();

        $subscription = $workspace->subscription();

        if (! $workspace->locked() && (! $subscription || ($subscription->valid()))) {
            abort(404);
        }

        return Inertia::render('Dashboard/Workspace/Locked');
    }
}
