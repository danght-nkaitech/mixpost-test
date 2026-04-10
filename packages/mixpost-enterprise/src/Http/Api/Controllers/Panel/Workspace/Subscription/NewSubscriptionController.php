<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Subscription\NewSubscription;

class NewSubscriptionController extends Controller
{
    use UsesWorkspaceModel;

    public function __invoke(NewSubscription $createSubscription): JsonResponse
    {
        $workspace = self::getWorkspaceModelClass()::firstOrFailByUuid($createSubscription->route('workspace'));

        WorkspaceManager::setCurrent($workspace);

        return response()->json($createSubscription->handle());
    }
}
