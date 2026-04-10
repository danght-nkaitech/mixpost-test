<?php

namespace Inovector\Mixpost\Http\Api\Controllers\Workspace\Post;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Api\Requests\Workspace\Post\ApprovePost;

class ApprovePostController extends Controller
{
    public function __invoke(ApprovePost $approvePost): JsonResponse
    {
        $approvePost->handle();

        return response()->json([
            'success' => true,
            'scheduled_at' => $approvePost->getDateTime()->toDateTimeString(),
        ]);
    }
}
