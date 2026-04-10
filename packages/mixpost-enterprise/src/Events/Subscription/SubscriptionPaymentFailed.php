<?php

namespace Inovector\MixpostEnterprise\Events\Subscription;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\MixpostEnterprise\Http\Base\Resources\WorkspaceResource;
use Inovector\MixpostEnterprise\Models\Workspace;

class SubscriptionPaymentFailed implements WebhookEvent
{
    use Dispatchable, SerializesModels;

    public Workspace $workspace;

    public array $payload;

    public function __construct(Workspace $workspace, array $payload)
    {
        $this->workspace = $workspace;
        $this->payload = $payload;
    }

    public static function name(): string
    {
        return 'subscription.payment_failed';
    }

    public static function nameLocalized(): string
    {
        return __('mixpost-enterprise::webhook.event.subscription.payment_failed');
    }

    public function payload(): array
    {
        $this->workspace->load('owner');

        return (new WorkspaceResource($this->workspace))->resolve();
    }
}
