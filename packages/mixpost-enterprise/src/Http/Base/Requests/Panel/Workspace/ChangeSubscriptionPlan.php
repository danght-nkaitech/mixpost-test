<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inovector\MixpostEnterprise\Actions\Subscription\ChangeSubscriptionPlan as ChangeSubscriptionPlanAction;
use Inovector\MixpostEnterprise\Configs\BillingConfig;
use Inovector\MixpostEnterprise\Models\Plan;
use Inovector\MixpostEnterprise\Models\Workspace;

class ChangeSubscriptionPlan extends FormRequest
{
    public function rules(): array
    {
        return [
            'cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'plan_id' => ['required', 'exists:'.app(Plan::class)->getTable().',id'],
            'prorate' => ['required', 'boolean'],
            'billing_immediately' => ['required', 'boolean'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function handle(): void
    {
        $workspace = Workspace::firstOrFailByUuid($this->route('workspace'));

        $config = new BillingConfig;

        (new ChangeSubscriptionPlanAction)(
            $workspace,
            Plan::find($this->input('plan_id')),
            $this->input('cycle'),
            $this->input('prorate', $config->get('prorate')),
            $this->input('bill_immediately', $config->get('bill_immediately'))
        );
    }
}
