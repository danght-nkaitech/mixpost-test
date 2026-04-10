<?php

namespace Inovector\MixpostEnterprise\Events\Subscription;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\MixpostEnterprise\Http\Base\Resources\SubscriptionResource;
use Inovector\MixpostEnterprise\Models\Subscription;

class SubscriptionUpdated implements WebhookEvent
{
    use Dispatchable, SerializesModels;

    public Subscription $subscription;

    public array $payload;

    public function __construct(Subscription $subscription, array $payload)
    {
        $this->subscription = $subscription;
        $this->payload = $payload;
    }

    public static function name(): string
    {
        return 'subscription.updated';
    }

    public static function nameLocalized(): string
    {
        return __('mixpost-enterprise::webhook.event.subscription.updated');
    }

    public function payload(): array
    {
        if (! $this->subscription->relationLoaded('workspace')) {
            $this->subscription->load('workspace');
        }

        if (! $this->subscription->relationLoaded('planMonthly')) {
            $this->subscription->load('planMonthly');
        }

        if (! $this->subscription->relationLoaded('planYearly')) {
            $this->subscription->load('planYearly');
        }

        $this->subscription->workspace?->load('owner');

        return (new SubscriptionResource($this->subscription))->resolve();
    }
}
