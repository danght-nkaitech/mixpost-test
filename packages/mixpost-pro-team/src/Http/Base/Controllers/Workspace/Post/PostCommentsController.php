<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Post;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Concerns\UsesPostActivities;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Post\DeletePostComment;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Post\StorePostComment;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Post\UpdatePostComment;
use Inovector\Mixpost\Http\Base\Resources\PostActivityResource;

class PostCommentsController extends Controller
{
    use UsesPostActivities;

    public function view(Request $request): PostActivityResource
    {
        $activity = self::getActivity(
            post: $request->route('post'),
            activityUuid: $request->route('activity')
        );

        return new PostActivityResource($activity);
    }

    public function store(StorePostComment $storePostComment): PostActivityResource
    {
        return new PostActivityResource($storePostComment->handle());
    }

    public function update(UpdatePostComment $updatePostComment): JsonResponse
    {
        return response()->json([
            'success' => $updatePostComment->handle(),
        ]);
    }

    public function destroy(DeletePostComment $deletePostComment): JsonResponse
    {
        return response()->json($deletePostComment->handle());
    }
}
