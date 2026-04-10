<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\NewSubscription;

class NewSubscriptionController extends Controller
{
    public function __invoke(NewSubscription $createSubscription): JsonResponse
    {
        return response()->json($createSubscription->handle());
    }
}
