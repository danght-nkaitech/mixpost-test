<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Inovector\MixpostEnterprise\Actions\Subscription\CancelSubscription as CancelSubscriptionAction;
use Inovector\MixpostEnterprise\Models\Workspace;

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
        $workspace = Workspace::firstOrFailByUuid($this->route('workspace'));

        $subscription = $workspace->subscription();

        if (! $subscription || ! $subscription->active()) {
            throw ValidationException::withMessages([
                'subscription' => __('mixpost-enterprise::workspace.workspace_no_active_subscription'),
            ]);
        }

        (new CancelSubscriptionAction($subscription))->cancel();
    }
}
