<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Configs\BillingConfig;
use Inovector\MixpostEnterprise\Http\Base\Resources\PlanResource;
use Inovector\MixpostEnterprise\Http\Base\Resources\SubscriptionResource;
use Inovector\MixpostEnterprise\Http\Base\Resources\WorkspaceResource;
use Inovector\MixpostEnterprise\Models\Plan;
use Inovector\MixpostEnterprise\PaymentPlatform;

class UpgradeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $workspace = WorkspaceManager::current();

        $workspace->load('genericSubscriptionPlan');

        $subscription = $workspace->subscription();

        $subscription?->load(['planMonthly', 'planYearly']);

        $paymentPlatform = PaymentPlatform::activePlatformInstance();

        return Inertia::render('Dashboard/Workspace/Upgrade', [
            'workspace' => new WorkspaceResource($workspace),
            'billingConfigs' => app(BillingConfig::class)->all(),
            'subscription' => $subscription ? new SubscriptionResource($subscription) : null,
            'plans' => PlanResource::collection(Plan::active()->paid()->get())->resolve(),
            'paymentSupportTrialing' => $paymentPlatform->supportTrialing(),
            'paymentSupportCoupon' => $paymentPlatform->supportCoupon(),
        ]);
    }
}
