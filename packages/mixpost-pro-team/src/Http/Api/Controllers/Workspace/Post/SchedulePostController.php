<?php

namespace Inovector\Mixpost\Http\Api\Controllers\Workspace\Post;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Api\Requests\Workspace\Post\SchedulePost;

class SchedulePostController extends Controller
{
    public function __invoke(SchedulePost $schedulePost): JsonResponse
    {
        $schedulePost->handle();

        return response()->json([
            'success' => true,
            'scheduled_at' => $schedulePost->getDateTime()->toDateTimeString(),
            'needs_approval' => $schedulePost->getPost()->isNeedsApproval(),
        ]);
    }
}
