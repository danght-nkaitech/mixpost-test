<?php

namespace Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Subscription;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\MixpostEnterprise\Enums\SubscriptionStatus;
use Inovector\MixpostEnterprise\Events\Subscription\SubscriptionCreated;
use Inovector\MixpostEnterprise\Models\Subscription;

class StoreSubscription extends FormRequest
{
    use UsesWorkspaceModel;

    public function rules(): array
    {
        return [
            'platform_subscription_id' => ['required', 'unique:'.Subscription::class.',platform_subscription_id'],
            'platform_plan_id' => ['required'],
            'status' => ['required', Rule::in(Arr::map(SubscriptionStatus::cases(), fn ($item) => $item->value))],
            'trial_ends_at' => ['required_if:status,'.SubscriptionStatus::TRIALING->value, 'nullable', 'date'],
        ];
    }

    public function handle(): Subscription
    {
        $workspace = self::getWorkspaceModelClass()::firstOrFailByUuid($this->route('workspace'));

        $subscription = $workspace->subscriptions()->create([
            'name' => 'default',
            'platform_subscription_id' => $this->input('platform_subscription_id'),
            'platform_plan_id' => $this->input('platform_plan_id'),
            'status' => $this->input('status'),
            'quantity' => 1,
            'trial_ends_at' => $this->input('trial_ends_at'),
        ]);

        SubscriptionCreated::dispatch($subscription->setRelation('workspace', $workspace), []);

        return $subscription;
    }
}
