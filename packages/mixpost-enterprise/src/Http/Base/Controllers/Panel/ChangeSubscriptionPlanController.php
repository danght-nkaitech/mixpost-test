<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace\ChangeSubscriptionPlan;
use Inovector\MixpostEnterprise\PaymentPlatform;

class ChangeSubscriptionPlanController extends Controller
{
    public function __invoke(ChangeSubscriptionPlan $swapSubscriptionPlan): RedirectResponse
    {
        $platformInstance = PaymentPlatform::activePlatformInstance();

        if (! $platformInstance->supportSwapSubscription()) {
            return redirect()->back()->with('error', __('This payment platform does not support changing subscription plan.'));
        }

        $swapSubscriptionPlan->handle();

        return redirect()->back()->with('success', __('mixpost-enterprise::subscription.sub_plan_changed'));
    }
}
