<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inovector\MixpostEnterprise\Metrics\Subscriptions;
use Inovector\MixpostEnterprise\Metrics\Users;
use Inovector\MixpostEnterprise\Metrics\Workspaces;

class MetricsController
{
    public function users(Request $request): JsonResponse
    {
        return response()->json(Users::make()->calculate($request));
    }

    public function workspaces(Request $request): JsonResponse
    {
        return response()->json(Workspaces::make()->calculate($request));
    }

    public function subscriptions(Request $request): JsonResponse
    {
        return response()->json(Subscriptions::make()->calculate($request));
    }
}
