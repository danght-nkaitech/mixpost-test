<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\DowngradeSubscriptionToFreePlan;

class DowngradeSubscriptionToFreePlanController extends Controller
{
    public function __invoke(DowngradeSubscriptionToFreePlan $downgradeSubscriptionToFreePlan): RedirectResponse
    {
        $downgradeSubscriptionToFreePlan->handle();

        return redirect()
            ->route('mixpost_e.workspace.billing', ['workspace' => WorkspaceManager::current()->uuid])
            ->with('success', __('mixpost-enterprise::subscription.sub_downgraded'));
    }
}
