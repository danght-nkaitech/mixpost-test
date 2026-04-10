<?php

namespace Inovector\Mixpost\Jobs\Webhook;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Webhook;

class ProcessWorkspaceWebhooksJob implements QueueWorkspaceAware, ShouldQueue
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
        Webhook::byWorkspace(WorkspaceManager::current()->id)
            ->active()
            ->whereJsonContains('events', $this->event->name())
            ->lazy()
            ->each(function (Webhook $webhook) {
                TriggerWorkspaceWebhookJob::dispatch($webhook, $this->event);
            });
    }
}
