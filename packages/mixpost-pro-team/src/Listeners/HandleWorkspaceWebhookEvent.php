<?php

namespace Inovector\Mixpost\Listeners;

use Exception;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Jobs\Webhook\ProcessWorkspaceWebhooksJob;

class HandleWorkspaceWebhookEvent
{
    public function handle(WebhookEvent $event): void
    {
        if (! WorkspaceManager::current()) {
            throw new Exception('Current workspace is not set.');
        }

        ProcessWorkspaceWebhooksJob::dispatch($event);
    }
}
