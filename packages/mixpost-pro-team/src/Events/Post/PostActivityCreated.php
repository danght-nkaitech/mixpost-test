<?php

namespace Inovector\Mixpost\Events\Post;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Http\Base\Resources\PostActivityResource;
use Inovector\Mixpost\Models\PostActivity;

class PostActivityCreated implements QueueWorkspaceAware, ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $deleteWhenMissingModels = true;

    public PostActivity $activity;

    public function __construct(PostActivity $activity)
    {
        $this->activity = $activity;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('mixpost_posts.'.$this->activity->post?->uuid);
    }

    public function broadcastWith(): array
    {
        return (new PostActivityResource($this->activity->load(['user', 'reactions.user'])->loadCount('children')))->resolve();
    }
}
