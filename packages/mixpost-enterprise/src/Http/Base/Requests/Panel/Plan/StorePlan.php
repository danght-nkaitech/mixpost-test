<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Plan;

use Inovector\MixpostEnterprise\Models\Plan;

class StorePlan extends PlanForm
{
    public function handle(): Plan
    {
        $this->throwFailedDuplicationPlanFreePlanException();

        return Plan::create($this->all());
    }
}
