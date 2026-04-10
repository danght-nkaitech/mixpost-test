<?php

namespace Inovector\Mixpost\Listeners\Post;

use Inovector\Mixpost\Events\Post\PostDeleteFromSocialPlatforms;
use Inovector\Mixpost\Jobs\Post\DeletePostFromSocialPlatformJob;
use Inovector\Mixpost\Models\Post;

class HandlePostDeleteFromSocialPlatformsEvent
{
    public function handle(PostDeleteFromSocialPlatforms $event): void
    {
        $posts = Post::whereIn('uuid', $event->uuids)
            ->whereHas('accounts', function ($query) {
                $query->whereNotNull('provider_post_id');
            })
            ->withTrashed()
            ->with('accounts')
            ->get();

        foreach ($posts as $post) {
            foreach ($post->accounts as $account) {
                // TODO: Check for each account provider type if it supports post deletion
                // and if the post was actually created on that platform.
                // For example, if the post was created on Facebook story type, we should not try to delete
                if (! $account->providerSupportsDeletion() || $account->pivot->provider_post_id === null) {
                    continue;
                }

                $data = json_decode($account->pivot->data ?? '{}', true);

                DeletePostFromSocialPlatformJob::dispatch($account, $account->pivot->provider_post_id, $data, $post, $event->user_id);
            }
        }
    }
}
