<?php

namespace Inovector\Mixpost\Listeners\Post;

use Inovector\Mixpost\Events\Post\PostActivityCreated;
use Inovector\Mixpost\Jobs\Post\SendNotificationsForActivitiesJob;

class HandlePostActivityCreatedEvent
{
    public function handle(PostActivityCreated $event): void
    {
        SendNotificationsForActivitiesJob::dispatch($event->activity);
    }
}
