<?php

namespace Inovector\Mixpost\Events\Post;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;

class PostCommentDeleted implements QueueWorkspaceAware, ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $postUuid;

    public string $activityUuid;

    public function __construct(string $postUuid, string $activityUuid)
    {
        $this->postUuid = $postUuid;
        $this->activityUuid = $activityUuid;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('mixpost_posts.'.$this->postUuid);
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->activityUuid,
        ];
    }
}
