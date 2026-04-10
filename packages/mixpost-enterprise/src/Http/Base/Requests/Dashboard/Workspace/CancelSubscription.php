<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Actions\Subscription\CancelSubscription as CancelSubscriptionAction;

class CancelSubscription extends FormRequest
{
    public function rules(): array
    {
        return [
            //
        ];
    }

    public function handle(): void
    {
        $workspace = WorkspaceManager::current();

        $subscription = $workspace->subscription();

        if (! $subscription || ! $subscription->active()) {
            throw ValidationException::withMessages([
                'subscription' => __('mixpost-enterprise::subscription.dont_have_active_sub'),
            ]);
        }

        (new CancelSubscriptionAction($subscription))->cancel();
    }
}
