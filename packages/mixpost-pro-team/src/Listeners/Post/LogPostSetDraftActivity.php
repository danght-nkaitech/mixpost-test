<?php

namespace Inovector\Mixpost\Listeners\Post;

use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Contracts\ShouldReceivePostModel;

class LogPostSetDraftActivity
{
    public function handle(ShouldReceivePostModel $event): void
    {
        $userId = Auth::id() ?? 0;

        $event->post->logSetDraftActivity($userId);
    }
}
