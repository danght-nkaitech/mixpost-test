<?php

namespace Inovector\Mixpost\Events\Post;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\ShouldReceivePostModel;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\Mixpost\Http\Api\Resources\PostResource;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Support\EagerLoadPostVersionsMedia;

class PostScheduled implements ShouldReceivePostModel, WebhookEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Post $post, public readonly bool $withApproval = false) {}

    public static function name(): string
    {
        return 'post.scheduled';
    }

    public static function nameLocalized(): string
    {
        return __('mixpost::webhook.event.post.scheduled');
    }

    public function payload(): array
    {
        $this->post->load('accounts', 'versions', 'tags');

        EagerLoadPostVersionsMedia::apply($this->post);

        return (new PostResource($this->post))->resolve();
    }
}
