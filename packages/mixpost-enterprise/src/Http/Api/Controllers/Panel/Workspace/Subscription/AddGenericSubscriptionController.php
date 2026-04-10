<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Subscription\AddGenericSubscription;

class AddGenericSubscriptionController extends Controller
{
    public function __invoke(AddGenericSubscription $addGenericSubscription): JsonResponse
    {
        $addGenericSubscription->handle();

        return response()->json([
            'success' => true,
        ]);
    }
}
