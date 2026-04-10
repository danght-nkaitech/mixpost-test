<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Post;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Post\AddPostToQueue;
use Inovector\Mixpost\Util;

class AddPostToQueueController extends Controller
{
    public function __invoke(AddPostToQueue $addPostToQueue): JsonResponse
    {
        $scheduledPost = $addPostToQueue->handle();

        $scheduledAt = $scheduledPost->scheduled_at
            ->tz(Settings::get('timezone'))
            ->translatedFormat('D, M j, '.Util::timeFormat());

        return response()->json([
            'scheduled_at' => $scheduledAt,
            'date' => $scheduledPost->scheduled_at->toDateString(),
            'needs_approval' => $scheduledPost->isNeedsApproval(),
        ]);
    }
}
