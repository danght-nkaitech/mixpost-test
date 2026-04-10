<?php

namespace Inovector\Mixpost\Http\Api\Controllers\Workspace\Post;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Actions\Post\DeletePost as DeletePostAction;
use Inovector\Mixpost\Enums\PostDeleteMode;
use Inovector\Mixpost\Http\Api\Requests\Workspace\Post\DeletePost;

class DeletePostsController extends Controller
{
    public function __invoke(DeletePost $request): JsonResponse
    {
        $result = (new DeletePostAction)(
            uuids: $request->input('posts'),
            mode: PostDeleteMode::from(
                $request->input('delete_mode', PostDeleteMode::APP_ONLY->value)
            ),
            toTrash: $toTrash = (bool) $request->get('trash'),
            userId: Auth::id()
        );

        if (! $toTrash) {
            return response()->json(array_merge($result, [
                'deleted' => $result['deleted_from_app'] ?? 0,
            ]));
        }

        return response()->json(array_merge($result, [
            'to_trash' => $result['deleted_from_app'] ?? 0,
        ]));
    }
}
