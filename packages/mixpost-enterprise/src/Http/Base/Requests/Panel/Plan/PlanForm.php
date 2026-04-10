<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Plan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inovector\MixpostEnterprise\Enums\PlanType;
use Inovector\MixpostEnterprise\Models\Plan;

class PlanForm extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(PlanType::FREE->value, PlanType::PAID->value)],
            'enabled' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer'],
            'price.monthly.amount' => [Rule::requiredIf($this->input('type') === PlanType::PAID->value), 'numeric'],
            'price.monthly.platform_plan_id' => [Rule::requiredIf($this->input('type') === PlanType::PAID->value), 'max:255'],
            'price.yearly.amount' => [Rule::requiredIf($this->input('type') === PlanType::PAID->value), 'numeric'],
            'price.yearly.platform_plan_id' => [Rule::requiredIf($this->input('type') === PlanType::PAID->value), 'max:255'],
            'limits' => ['array'],
            'limits.*.code' => ['required', 'string'], // TODO: check if feature code is exists FeatureLimit::list()
            'limits.*.form.*.name' => ['required', 'string'],
            'limits.*.form.*.value' => ['required'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'monthly_amount' => $this->input('price.monthly.amount'),
            'monthly_platform_plan_id' => $this->input('price.monthly.platform_plan_id'),
            'yearly_amount' => $this->input('price.yearly.amount'),
            'yearly_platform_plan_id' => $this->input('price.yearly.platform_plan_id'),
        ]);
    }

    protected function throwFailedDuplicationPlanFreePlanException(): void
    {
        if ($this->input('type') !== PlanType::FREE->value) {
            return;
        }

        $freePlan = Plan::query()->free()->first();

        if ($freePlan && (! $this->route('plan') || $freePlan->id !== (int) $this->route('plan'))) {
            throw ValidationException::withMessages([
                'type' => __('mixpost-enterprise::plan.free_plan_exists'),
            ]);
        }
    }
}
