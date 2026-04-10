<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Post;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Post\SchedulePost;
use Inovector\Mixpost\Util;

class SchedulePostController extends Controller
{
    public function __invoke(SchedulePost $schedulePost): JsonResponse
    {
        $schedulePost->handle();

        $scheduledAt = $schedulePost->getDateTime()->tz(Settings::get('timezone'))->translatedFormat('D, M j, '.Util::timeFormat());

        return response()->json([
            'scheduled_at' => $scheduledAt,
            'date' => $schedulePost->getDateTime()->toDateString(),
            'needs_approval' => $schedulePost->getPost()->isNeedsApproval(),
        ]);
    }
}
