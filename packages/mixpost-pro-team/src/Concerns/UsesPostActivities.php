<?php

namespace Inovector\Mixpost\Concerns;

use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Models\PostActivity;

trait UsesPostActivities
{
    public static function getActivity(string|Post $post, string $activityUuid): ?PostActivity
    {
        if (! $post instanceof Post) {
            return Post::findByUuid($post)
                ?->activities()
                ->where('uuid', $activityUuid)
                ->first();
        }

        return $post->activities()
            ->where('uuid', $activityUuid)
            ->first();
    }
}
