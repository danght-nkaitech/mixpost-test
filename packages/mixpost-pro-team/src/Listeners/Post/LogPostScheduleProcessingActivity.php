<?php

namespace Inovector\Mixpost\Listeners\Post;

use Inovector\Mixpost\Contracts\ShouldReceivePostModel;

class LogPostScheduleProcessingActivity
{
    public function handle(ShouldReceivePostModel $event): void
    {
        $event->post->logScheduleProcessing();
    }
}
