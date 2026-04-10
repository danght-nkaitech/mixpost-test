<?php

namespace Inovector\MixpostEnterprise\Actions\Workspace;

use Inovector\MixpostEnterprise\Actions\Subscription\NewGenericSubscription;
use Inovector\MixpostEnterprise\Models\Plan;
use Inovector\MixpostEnterprise\Models\Workspace;

class OnboardWorkspace
{
    public function __invoke(Workspace|int $workspace): void
    {
        if ($plan = Plan::active()->free()->first()) {
            if (is_int($workspace)) {
                $workspace = Workspace::findOrFail($workspace);
            }

            (new NewGenericSubscription)(
                workspace: $workspace,
                plan: $plan,
                withTrial: false
            );
        }
    }
}
