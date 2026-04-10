<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Subscription\ChangeSubscriptionPlan as ChangeSubscriptionPlanRequest;

class ChangeSubscriptionPlanController extends Controller
{
    public function __invoke(ChangeSubscriptionPlanRequest $changeSubscriptionPlan): JsonResponse
    {
        $changeSubscriptionPlan->handle();

        return response()->json([
            'success' => true,
        ]);
    }
}
