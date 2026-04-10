<?php

namespace Inovector\Mixpost\Listeners\Post;

use Inovector\Mixpost\Contracts\ShouldReceivePostModel;

class LogPostCreatedActivity
{
    public function handle(ShouldReceivePostModel $event): void
    {
        $event->post->logCreatedActivity();
    }
}
