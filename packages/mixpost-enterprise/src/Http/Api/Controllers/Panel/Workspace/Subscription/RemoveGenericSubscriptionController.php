<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;

class RemoveGenericSubscriptionController extends Controller
{
    use UsesWorkspaceModel;

    public function __invoke(Request $request): JsonResponse
    {
        $workspace = self::getWorkspaceModelClass()::firstOrFailByUuid($request->route('workspace'));

        $workspace->saveLimits([]);
        $workspace->removeGenericSubscription();

        return response()->json([
            'success' => true,
        ]);
    }
}
