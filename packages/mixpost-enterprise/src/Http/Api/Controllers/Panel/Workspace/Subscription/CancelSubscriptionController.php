<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Subscription\CancelSubscription;

class CancelSubscriptionController extends Controller
{
    public function __invoke(CancelSubscription $cancelSubscription): JsonResponse
    {
        $cancelSubscription->handle();

        return response()->json([
            'success' => true,
        ]);
    }
}
