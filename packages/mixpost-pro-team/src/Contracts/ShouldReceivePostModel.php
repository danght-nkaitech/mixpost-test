<?php

namespace Inovector\Mixpost\Contracts;

use Inovector\Mixpost\Models\Post;

interface ShouldReceivePostModel
{
    public function __construct(Post $post);
}
