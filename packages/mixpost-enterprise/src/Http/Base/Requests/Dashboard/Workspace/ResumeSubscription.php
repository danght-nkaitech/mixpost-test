<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Inovector\Mixpost\Facades\WorkspaceManager;

class ResumeSubscription extends FormRequest
{
    public function rules(): array
    {
        return [];
    }

    /**
     * @throws ValidationException
     */
    public function handle(): void
    {
        $workspace = WorkspaceManager::current();

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
