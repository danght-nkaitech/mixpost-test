<?php

namespace Inovector\MixpostEnterprise\Actions\Subscription;

use Illuminate\Validation\ValidationException;
use Inovector\MixpostEnterprise\Models\Plan;
use Inovector\MixpostEnterprise\Models\Workspace;

class ChangeSubscriptionPlan
{
    public function __invoke(Workspace $workspace,
        Plan $plan,
        $cycle,
        bool $prorate,
        bool $billImmediately
    ): void {
        if ($plan->free()) {
            (new DowngradeToFreePlanSubscription)($workspace);

            return;
        }

        $subscription = $workspace->subscription();

        if (! $subscription) {
            throw ValidationException::withMessages([
                'subscription' => __('mixpost-enterprise::subscription.not_found'),
            ]);
        }

        if (! $subscription || ! $subscription->recurring()) {
            throw ValidationException::withMessages([
                'subscription' => __('mixpost-enterprise::subscription.cant_swap_plan'),
            ]);
        }

        $platformPlanId = Plan::platformPlanId($plan, $cycle);

        if (! $platformPlanId) {
            throw ValidationException::withMessages([
                'plan_id' => __('mixpost-enterprise::plan.platform_plan_not_found'),
            ]);
        }

        if ($subscription->hasPlan($platformPlanId)) {
            throw ValidationException::withMessages([
                'plan_id' => __('mixpost-enterprise::subscription.already_subscribed'),
            ]);
        }

        $subscription->setProrate($prorate);

        if ($billImmediately) {
            $subscription->swapAndInvoice($platformPlanId);

            return;
        }

        $subscription->swap($platformPlanId);
    }
}
