<?php

namespace Inovector\Mixpost\Listeners\Post;

use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Events\Post\PostScheduleAtUpdated;

class LogPostUpdatedScheduledTimeActivity
{
    public function handle(PostScheduleAtUpdated $event): void
    {
        $event->post->refresh();

        $userId = Auth::id() ?? 0;

        $event->post->logUpdatedScheduleTimeActivity(
            user: $userId,
            data: [
                'old_scheduled_at' => $event->oldScheduledAtValue,
                'new_scheduled_at' => $event->post->scheduled_at,
            ]
        );
    }
}
