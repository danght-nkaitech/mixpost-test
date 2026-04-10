<?php

namespace Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Subscription;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\MixpostEnterprise\Exceptions\NoPaymentPlatformActiveException;
use LogicException;

class ResumeSubscription extends FormRequest
{
    use UsesWorkspaceModel;

    public function rules(): array
    {
        return [];
    }

    /**
     * @throws LogicException|NoPaymentPlatformActiveException
     */
    public function handle(): void
    {
        $workspace = self::getWorkspaceModelClass()::firstOrFailByUuid($this->route('workspace'));

        $subscription = $workspace->subscription();

        if (! $subscription) {
            throw ValidationException::withMessages([
                'subscription' => __('mixpost-enterprise::subscription.not_found'),
            ]);
        }

        if (! $subscription->canBeResumed()) {
            throw ValidationException::withMessages([
                'subscription' => __('mixpost-enterprise::subscription.cannot_resume'),
            ]);
        }

        $subscription->resume();
    }
}
