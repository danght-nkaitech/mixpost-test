<?php

namespace Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Subscription;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionDeleted;

class DeleteSubscription extends FormRequest
{
    use UsesWorkspaceModel;

    public function handle(): bool
    {
        $workspace = self::getWorkspaceModelClass()::firstOrFailByUuid($this->route('workspace'));

        $subscription = $workspace->subscription();

        if (! $subscription) {
            throw ValidationException::withMessages([
                'subscription' => __('mixpost-enterprise::subscription.not_found'),
            ]);
        }

        $delete = $subscription->delete();

        SubscriptionDeleted::dispatch($workspace);

        return $delete;
    }
}
