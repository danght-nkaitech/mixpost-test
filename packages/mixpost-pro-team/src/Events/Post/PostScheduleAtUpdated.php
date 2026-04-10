<?php

namespace Inovector\Mixpost\Events\Post;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Models\Post;

class PostScheduleAtUpdated
{
    use Dispatchable, SerializesModels;

    public Post $post;

    public ?Carbon $oldScheduledAtValue;

    public function __construct(Post $post, ?Carbon $oldScheduledAtValue = null)
    {
        $this->post = $post;
        $this->oldScheduledAtValue = $oldScheduledAtValue;
    }
}
