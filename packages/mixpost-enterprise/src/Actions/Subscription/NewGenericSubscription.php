<?php

namespace Inovector\MixpostEnterprise\Actions\Subscription;

use Illuminate\Support\Carbon;
use Inovector\MixpostEnterprise\Configs\BillingConfig;
use Inovector\MixpostEnterprise\Models\Plan;
use Inovector\MixpostEnterprise\Models\Workspace;

class NewGenericSubscription
{
    public function __invoke(Workspace $workspace, Plan $plan, $withTrial = true, $overrideTrialDays = null, $keepPrevTrialEndsAt = true): void
    {
        if ($workspace->unlimitedAccess()) {
            $workspace->saveAsAccessStatusSubscription();
        }

        $workspace->saveLimits($plan->limits);

        $trialEndsAt = $workspace->generic_trial_ends_at;

        if (! $trialEndsAt || ! $keepPrevTrialEndsAt) {
            $trialEndsAt = $plan->free() ? null : $this->trialEndsAt($withTrial, $overrideTrialDays);
        }

        $workspace->setGenericSubscription(
            plan: $plan,
            trialEndsAt: $trialEndsAt
        );
    }

    private function trialEndsAt($withTrial, $overrideTrialDays): ?Carbon
    {
        if (! $withTrial) {
            return null;
        }

        if ($overrideTrialDays !== null && (int) $overrideTrialDays === 0) {
            return null;
        }

        if ($overrideTrialDays) {
            return Carbon::now()->addDays((int) $overrideTrialDays);
        }

        $billingConfig = app(BillingConfig::class);
        $trialDays = $billingConfig->get('trial_days');

        if ($trialDays && $trialDays !== 0) {
            return Carbon::now()->addDays((int) $trialDays);
        }

        return null;
    }
}
