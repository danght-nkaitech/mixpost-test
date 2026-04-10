<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\ValidationException;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Actions\Subscription\NewGenericSubscription;
use Inovector\MixpostEnterprise\Configs\BillingConfig;
use Inovector\MixpostEnterprise\Models\Plan;

class NewSubscription extends FormRequest
{
    private ?BillingConfig $config = null;

    private ?int $remainingTrialDays = null;

    public function rules(): array
    {
        return [
            'cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'plan_id' => [
                'required',
                (new Exists(Plan::class, 'id'))->where('enabled', true),
            ],
            'coupon' => ['nullable', 'string'],
        ];
    }

    public function handle()
    {
        $workspace = WorkspaceManager::current();
        $subscription = $workspace->subscription();
        $plan = Plan::active()->paid()->where('id', $this->input('plan_id'))->first();
        $platformPlanId = Plan::platformPlanId($plan, $this->input('cycle'));

        if ($subscription && $subscription->recurring()) {
            throw ValidationException::withMessages([
                'subscription' => __('mixpost-enterprise::subscription.cant_subscribe_plan_have_active_sub'),
            ]);
        }

        $this->handleGenericSubscription($workspace, $plan);

        if (! $platformPlanId) {
            throw ValidationException::withMessages([
                'plan_id' => __('mixpost-enterprise::plan.platform_plan_not_found'),
            ]);
        }

        if ($subscription) {
            $this->handleExistingSubscription($subscription);
        }

        return $this->createSubscription($workspace, $platformPlanId);
    }

    private function handleGenericSubscription($workspace, $plan): void
    {
        if (! ($this->genericTrialEnabled() && $this->defaultTrialDays())) {
            return;
        }

        if ($workspace->hasGenericSubscription() && $workspace->hasGenericTrial()) {
            return;
        }

        if ($workspace->hasGenericSubscription() && $workspace->hasExpiredGenericTrial()) {
            return;
        }

        if ($workspace->subscription()) {
            return;
        }

        (new NewGenericSubscription)(
            workspace: $workspace,
            plan: $plan
        );

        throw ValidationException::withMessages([
            'created_generic_subscription' => __('mixpost-enterprise::subscription.sub_created'),
        ])->status(409);
    }

    private function genericTrialEnabled()
    {
        return $this->config()['generic_trial'];
    }

    private function config(): array
    {
        if (! $this->config) {
            $this->config = new BillingConfig;
        }

        return [
            'generic_trial' => $this->config->get('generic_trial'),
            'currency' => $this->config->get('currency'),
            'trial_days' => (int) $this->config->get('trial_days'),
        ];
    }

    private function defaultTrialDays()
    {
        return $this->config()['trial_days'];
    }

    private function handleExistingSubscription($subscription): void
    {
        if ($subscription->recurring()) {
            throw ValidationException::withMessages([
                'subscription' => __('mixpost-enterprise::subscription.already_subscribed'),
            ]);
        }

        if ($subscription->onTrial()) {
            $this->remainingTrialDays = $subscription->remainingTrialDays();
        }

        if ($subscription->hasExpiredTrial()) {
            $this->remainingTrialDays = 0;
        }
    }

    private function createSubscription($workspace, $platformPlanId)
    {
        $build = $workspace->newSubscription('default', $platformPlanId)
            ->returnTo(route('mixpost_e.workspace.billing', [
                'workspace' => $workspace->uuid,
                'delay' => true,
            ]))
            ->cancelUrl(route('mixpost_e.workspace.upgrade', [
                'workspace' => $workspace->uuid,
            ]))
            ->withMetaData([
                'currency' => $this->currency(),
            ]);

        if ($this->defaultTrialDays() && ! $this->genericTrialEnabled()) {
            $build->trialDays($this->remainingTrialDays !== null ? $this->remainingTrialDays : $this->defaultTrialDays());
        }

        if ($this->input('coupon')) {
            $build->useCoupon($this->input('coupon'));
        }

        return $build->create();
    }

    private function currency()
    {
        return $this->config()['currency'];
    }
}
