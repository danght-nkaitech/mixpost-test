<?php

namespace Inovector\Mixpost\Jobs\Webhook;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\Mixpost\Models\Webhook;

class ProcessSystemWebhooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deleteWhenMissingModels = true;

    public WebhookEvent $event;

    public function __construct(WebhookEvent $event)
    {
        $this->event = $event;
    }

    public function handle(): void
    {
        Webhook::system()
            ->active()
            ->whereJsonContains('events', $this->event->name())
            ->lazy()
            ->each(function (Webhook $webhook) {
                TriggerSystemWebhookJob::dispatch($webhook, $this->event);
            });
    }
}
