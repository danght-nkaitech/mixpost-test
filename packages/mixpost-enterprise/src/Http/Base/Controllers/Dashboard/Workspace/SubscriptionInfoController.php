<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Http\Base\Resources\SubscriptionInfoResource;

class SubscriptionInfoController extends Controller
{
    public function __invoke(Request $request): SubscriptionInfoResource|Response
    {
        $workspace = WorkspaceManager::current();

        $subscription = $workspace->subscription();

        if (! $subscription) {
            return response()->noContent();
        }

        return new SubscriptionInfoResource($subscription->subscriptionInfo());
    }
}
