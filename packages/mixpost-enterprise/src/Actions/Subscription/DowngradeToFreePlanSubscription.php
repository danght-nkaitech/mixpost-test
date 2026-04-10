<?php

namespace Inovector\MixpostEnterprise\Actions\Subscription;

use Illuminate\Support\Facades\DB;
use Inovector\MixpostEnterprise\Models\Plan;
use Inovector\MixpostEnterprise\Models\Workspace;
use LogicException;

class DowngradeToFreePlanSubscription
{
    public function __invoke(Workspace $workspace, bool $withTrial = false): void
    {
        if (! Plan::activeFreeExists()) {
            throw new LogicException('Active free plan not found.');
        }

        DB::transaction(function () use ($withTrial, $workspace) {
            (new NewGenericSubscription)(
                workspace: $workspace,
                plan: Plan::activeFreeFirst(),
                withTrial: $withTrial
            );

            try {
                (new CancelSubscription(
                    subscription: $workspace->subscription()
                ))->now();
            } catch (\Exception $e) {
            }

            $workspace->subscription()->delete();
        });
    }
}
