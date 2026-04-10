<?php

namespace Inovector\Mixpost\Http\Api\Controllers\Workspace\Post;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Api\Requests\Workspace\Post\AddPostToQueue;

class AddPostToQueueController extends Controller
{
    public function __invoke(AddPostToQueue $addPostToQueue): JsonResponse
    {
        $scheduledPost = $addPostToQueue->handle();

        return response()->json([
            'success' => true,
            'scheduled_at' => $scheduledPost->scheduled_at->toDateTimeString(),
            'needs_approval' => $scheduledPost->isNeedsApproval(),
        ]);
    }
}
