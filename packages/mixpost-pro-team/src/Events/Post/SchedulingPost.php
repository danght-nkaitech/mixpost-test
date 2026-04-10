<?php

namespace Inovector\Mixpost\Events\Post;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Models\Workspace;

class SchedulingPost
{
    use Dispatchable, SerializesModels;

    public ?Workspace $workspace;

    public Post $post;

    public Request $request;

    public function __construct(Post $post, Request $request)
    {
        $this->workspace = WorkspaceManager::current();
        $this->post = $post;
        $this->request = $request;
    }
}
