<?php

namespace Inovector\Mixpost\Events\Post;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostDeleteFromSocialPlatforms
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly int $user_id, public readonly array $uuids) {}
}
