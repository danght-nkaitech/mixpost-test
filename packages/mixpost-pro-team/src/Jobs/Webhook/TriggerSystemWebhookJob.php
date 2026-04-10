<?php

namespace Inovector\Mixpost\Jobs\Webhook;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\Mixpost\Models\Webhook;
use Inovector\Mixpost\Models\WebhookDelivery;

class TriggerSystemWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deleteWhenMissingModels = true;

    public Webhook $webhook;

    public WebhookEvent $event;

    public ?WebhookDelivery $parentDelivery = null;

    public function __construct(Webhook $webhook, WebhookEvent $event, ?WebhookDelivery $parentDelivery = null)
    {
        $this->webhook = $webhook;
        $this->event = $event;
        $this->parentDelivery = $parentDelivery;
    }

    public function handle(): void
    {
        if (! $this->webhook->isActive()) {
            return;
        }

        if ($this->parentDelivery?->isAttemptLimitReached()) {
            return;
        }

        $delivery = $this->webhook->trigger($this->event);
        $delivery->setRelation('webhook', $this->webhook);
        $delivery->attempt();
        $this->parentDelivery?->attempt();

        if (! $delivery->isSucceeded() && $this->webhook->max_attempts > 1 && ! $this->parentDelivery?->isAttemptLimitReached()) {
            $parentDelivery = $this->parentDelivery ?: $delivery;

            $delay = pow(2, $parentDelivery->attempts) * 60; // 2 minutes, 4 minutes, 8 minutes, ...

            if (! $this->parentDelivery) {
                $delivery->updateResendAt(Carbon::now()->utc()->addSeconds($delay));
            }

            self::dispatch($this->webhook, $this->event, $parentDelivery)->delay($delay);
        }
    }
}
