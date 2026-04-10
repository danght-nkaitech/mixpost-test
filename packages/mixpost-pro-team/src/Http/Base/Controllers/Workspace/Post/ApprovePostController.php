<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Post;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Post\ApprovePost;
use Inovector\Mixpost\Util;

class ApprovePostController extends Controller
{
    public function __invoke(ApprovePost $approvePost): JsonResponse
    {
        $approvePost->handle();

        $scheduledAt = $approvePost->getDateTime()->tz(Settings::get('timezone'))->translatedFormat('D, M j, '.Util::timeFormat());

        return response()->json([
            'scheduled_at' => $scheduledAt,
            'date' => $approvePost->getDateTime()->toDateString(),
        ]);
    }
}
