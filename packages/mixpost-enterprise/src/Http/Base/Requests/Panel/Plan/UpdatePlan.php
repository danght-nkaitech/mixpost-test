<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Plan;

use Inovector\MixpostEnterprise\Jobs\Subscription\UpdateWorkspacesLimitsJob;
use Inovector\MixpostEnterprise\Models\Plan;

class UpdatePlan extends PlanForm
{
    public function handle(): void
    {
        $this->throwFailedDuplicationPlanFreePlanException();

        $plan = Plan::findOrFail($this->route('plan'));

        $plan->update($this->except(['type']));

        UpdateWorkspacesLimitsJob::dispatch($plan);
    }
}
