<?php

namespace Inovector\Mixpost\Listeners\Post;

use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Contracts\ShouldReceivePostModel;

class LogPostScheduledActivity
{
    public function handle(ShouldReceivePostModel $event): void
    {
        $userId = Auth::id() ?? 0;

        $event->post->logScheduledActivity($userId, [
            'scheduled_at' => $event->post->getOriginal('scheduled_at'),
            'status' => strtolower($event->post->status->name),
            'with_approval' => $event->withApproval ?? false,
        ]);
    }
}
