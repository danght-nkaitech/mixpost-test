<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Concerns\UsesPostActivities;
use Inovector\Mixpost\Http\Base\Resources\PostActivityResource;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Models\PostActivity;

class PostCommentChildrenController extends Controller
{
    use UsesPostActivities;

    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $post = Post::firstOrFailByUuid($request->route('post'));

        $activity = self::getActivity(
            post: $post,
            activityUuid: $request->route('activity')
        );

        if (! $activity) {
            abort(404);
        }

        $records = PostActivity::where('post_id', $post->id)
            ->where('parent_id', $activity->id)
            ->with(['user', 'reactions.user'])
            ->simplePaginate(30);

        return PostActivityResource::collection($records);
    }
}
