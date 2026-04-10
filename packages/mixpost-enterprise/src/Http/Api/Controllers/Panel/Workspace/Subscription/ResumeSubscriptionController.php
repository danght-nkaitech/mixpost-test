<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Subscription\ResumeSubscription;

class ResumeSubscriptionController extends Controller
{
    public function __invoke(ResumeSubscription $resumeSubscription): JsonResponse
    {
        $resumeSubscription->handle();

        return response()->json([
            'success' => true,
        ]);
    }
}
