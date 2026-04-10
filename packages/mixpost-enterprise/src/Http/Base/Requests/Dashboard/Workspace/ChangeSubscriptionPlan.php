<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Actions\Subscription\ChangeSubscriptionPlan as ChangeSubscriptionPlanAction;
use Inovector\MixpostEnterprise\Configs\BillingConfig;
use Inovector\MixpostEnterprise\Models\Plan;

class ChangeSubscriptionPlan extends FormRequest
{
    public function rules(): array
    {
        return [
            'cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'plan_id' => ['required', 'exists:'.app(Plan::class)->getTable().',id'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function handle(): void
    {
        $workspace = WorkspaceManager::current();

        $config = new BillingConfig;

        (new ChangeSubscriptionPlanAction)(
            $workspace,
            Plan::find($this->input('plan_id')),
            $this->input('cycle'),
            $config->get('prorate'),
            $config->get('bill_immediately')
        );
    }
}
