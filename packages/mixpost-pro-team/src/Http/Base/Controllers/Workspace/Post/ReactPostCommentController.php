<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Post;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Post\ReactPostComment;

class ReactPostCommentController extends Controller
{
    public function __invoke(ReactPostComment $reactPostComment): JsonResponse
    {
        return response()->json([
            'toggle' => $reactPostComment->handle()->name,
            'reaction' => $reactPostComment->input('reaction'),
        ]);
    }
}
