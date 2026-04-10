<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\ChangeSubscriptionPlan;
use Inovector\MixpostEnterprise\PaymentPlatform;

class ChangeSubscriptionPlanController extends Controller
{
    public function __invoke(ChangeSubscriptionPlan $swapSubscriptionPlan): RedirectResponse
    {
        $platformInstance = PaymentPlatform::activePlatformInstance();

        if (! $platformInstance->supportSwapSubscription()) {
            return redirect()
                ->route('mixpost_e.workspace.billing', ['workspace' => WorkspaceManager::current()->uuid])
                ->with('warning', __('mixpost-enterprise::subscription.need_cancel_to_swap'));
        }

        $swapSubscriptionPlan->handle();

        return redirect()
            ->route('mixpost_e.workspace.billing', ['workspace' => WorkspaceManager::current()->uuid])
            ->with('success', __('mixpost-enterprise::subscription.sub_plan_changed'));
    }
}
