<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Resources\PostActivityResource;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Models\PostActivity;

class PostActivitiesController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $post = Post::firstOrFailTrashedByUuid($request->route('post'));

        $records = PostActivity::where('post_id', $post->id)
            ->whereNull('parent_id')
            ->with(['user', 'reactions.user'])
            ->withCount('children')
            ->oldest('id')
            ->get();

        return PostActivityResource::collection($records);
    }
}
