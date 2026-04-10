<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Actions\Subscription\DowngradeToFreePlanSubscription;
use Inovector\MixpostEnterprise\Models\Plan;

class DowngradeSubscriptionToFreePlan extends FormRequest
{
    public function rules(): array
    {
        return [];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! Plan::activeFreeExists()) {
                $validator->errors()->add('plan', __('mixpost-enterprise::subscription.active_free_plan_not_found'));
            }

            if (WorkspaceManager::current()->genericSubscriptionFree()) {
                $validator->errors()->add('generic_subscription_free', __('mixpost-enterprise::subscription.sub_already_free'));
            }
        });
    }

    public function handle(): void
    {
        (new DowngradeToFreePlanSubscription)(WorkspaceManager::current());
    }
}
