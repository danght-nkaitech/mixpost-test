<?php

namespace Inovector\MixpostEnterprise\Events\Subscription;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\MixpostEnterprise\Http\Base\Resources\ReceiptResource;
use Inovector\MixpostEnterprise\Models\Receipt;

class SubscriptionPaymentSucceeded implements WebhookEvent
{
    use Dispatchable, SerializesModels;

    public Receipt $receipt;

    public array $payload;

    public function __construct(Receipt $receipt, array $payload)
    {
        $this->receipt = $receipt;
        $this->payload = $payload;
    }

    public static function name(): string
    {
        return 'subscription.payment_succeeded';
    }

    public static function nameLocalized(): string
    {
        return __('mixpost-enterprise::webhook.event.subscription.payment_succeeded');
    }

    public function payload(): array
    {
        if (! $this->receipt->relationLoaded('workspace')) {
            $this->receipt->load('workspace');
        }

        $this->receipt->workspace?->load('owner');

        return (new ReceiptResource($this->receipt))->resolve();
    }
}
