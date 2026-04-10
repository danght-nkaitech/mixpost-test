<?php

namespace Inovector\Mixpost\Listeners;

use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\Mixpost\Jobs\Webhook\ProcessSystemWebhooksJob;

class HandleSystemWebhookEvent
{
    public function handle(WebhookEvent $event): void
    {
        ProcessSystemWebhooksJob::dispatch($event);
    }
}
