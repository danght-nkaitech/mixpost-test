<?php

namespace Inovector\Mixpost\Listeners\Post;

use Inovector\Mixpost\Contracts\ShouldReceivePostModel;

class LogPostPublishedActivity
{
    public function handle(ShouldReceivePostModel $event): void
    {
        $event->post->logPublishedActivity();
    }
}
