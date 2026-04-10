<?php

namespace Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Subscription;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\MixpostEnterprise\Enums\SubscriptionStatus;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionUpdated;

class UpdateSubscription extends FormRequest
{
    use UsesWorkspaceModel;

    public function rules(): array
    {
        return [
            'platform_plan_id' => ['required'],
            'status' => ['required', Rule::in(Arr::map(SubscriptionStatus::cases(), fn ($item) => $item->value))],
            'trial_ends_at' => ['required_if:status,'.SubscriptionStatus::TRIALING->value, 'nullable', 'date'],
            'paused_from' => ['required_if:status,'.SubscriptionStatus::PAUSED->value, 'nullable', 'date'],
        ];
    }

    public function handle(): bool
    {
        $workspace = self::getWorkspaceModelClass()::firstOrFailByUuid($this->route('workspace'));

        $subscription = $workspace->subscription();

        if (! $subscription) {
            throw ValidationException::withMessages([
                'subscription' => __('mixpost-enterprise::subscription.not_found'),
            ]);
        }

        $update = $subscription->update([
            'platform_plan_id' => $this->input('platform_plan_id'),
            'status' => $this->input('status'),
            'trial_ends_at' => $this->input('trial_ends_at'),
            'paused_from' => $this->input('paused_from'),
        ]);

        $subscription->refresh();

        SubscriptionUpdated::dispatch($subscription->setRelation('workspace', $workspace), []);

        return $update;
    }
}
