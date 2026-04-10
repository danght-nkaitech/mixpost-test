<?php

namespace Inovector\Mixpost\Events\Post;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\ShouldReceivePostModel;
use Inovector\Mixpost\Models\Post;

class PostScheduleProcessing implements ShouldReceivePostModel
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Post $post) {}
}
