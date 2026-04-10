<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\MixpostEnterprise\Actions\Subscription\CancelSubscription;
use Inovector\MixpostEnterprise\Actions\Subscription\NewGenericSubscription;
use Inovector\MixpostEnterprise\Models\Plan;
use Inovector\MixpostEnterprise\Models\Workspace;

class AddGenericSubscription extends FormRequest
{
    public function rules(): array
    {
        return [
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'plan_id' => ['required', 'exists:'.Plan::class.',id'],
            'keep_prev_trial_ends_at' => ['required', 'boolean'],
        ];
    }

    public function handle(): void
    {
        $workspace = Workspace::firstOrFailByUuid($this->route('workspace'));

        $plan = Plan::find($this->input('plan_id'));

        if ($subscription = $workspace->subscription()) {
            try {
                (new CancelSubscription(
                    subscription: $subscription
                ))->now();
            } catch (\Exception $e) {
            }

            $subscription->delete();
        }

        (new NewGenericSubscription)(
            workspace: $workspace,
            plan: $plan,
            overrideTrialDays: $this->input('trial_days'),
            keepPrevTrialEndsAt: $this->input('keep_prev_trial_ends_at', false)
        );
    }
}
