<?php

namespace Inovector\Mixpost\Listeners\Post;

use Inovector\Mixpost\Contracts\ShouldReceivePostModel;

class LogPostPublishedFailedActivity
{
    public function handle(ShouldReceivePostModel $event): void
    {
        $event->post->logPublishedFailedActivity();
    }
}
