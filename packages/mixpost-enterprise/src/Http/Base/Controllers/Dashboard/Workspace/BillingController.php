<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Configs\BillingConfig;
use Inovector\MixpostEnterprise\Http\Base\Resources\SubscriptionResource;
use Inovector\MixpostEnterprise\Http\Base\Resources\WorkspaceResource;
use Inovector\MixpostEnterprise\Models\Plan;
use Inovector\MixpostEnterprise\PaymentPlatform;

class BillingController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $workspace = WorkspaceManager::current();

        $workspace->load('genericSubscriptionPlan');

        $subscription = $workspace->subscription();

        $subscription?->load(['planMonthly', 'planYearly']);

        $paymentPlatform = PaymentPlatform::activePlatformInstance();

        return Inertia::render('Dashboard/Workspace/Billing', [
            'workspace' => (new WorkspaceResource($workspace))->additionalFields([
                'payment_method' => [
                    'type' => $workspace->pm_type,
                    'card_brand' => $workspace->pm_card_brand,
                    'card_last_four' => $workspace->pm_card_last_four,
                    'card_expires' => $workspace->pm_card_expires,
                ],
            ]),
            'subscription' => $subscription ? (new SubscriptionResource($subscription))->except(['platform_plan_id', 'platform_subscription_id']) : null,
            'currency' => app(BillingConfig::class)->get('currency'),
            'hasDelay' => (bool) $request->get('delay', false),
            'freePlanExists' => Plan::activeFreeExists(),
            'can_be_resumed' => $paymentPlatform->supportResumeSubscription() && $subscription?->canBeResumed(),
        ]);
    }
}
