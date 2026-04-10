<?php

namespace Inovector\Mixpost\Listeners\Post;

use Inovector\Mixpost\Contracts\ShouldReceivePostModel;
use Inovector\Mixpost\Features;

class SubscribePostToActivitiesNotification
{
    public function handle(ShouldReceivePostModel $event): void
    {
        if (! Features::isAutoSubscribePostActivitiesEnabled()) {
            return;
        }

        $event->post->subscribeToActivitiesNotifications(user: $event->post->user_id);
    }
}
