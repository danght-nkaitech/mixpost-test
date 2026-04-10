<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Resources\SubscriptionInfoResource;
use Inovector\MixpostEnterprise\Models\Workspace;

class SubscriptionInfoController extends Controller
{
    public function __invoke(Request $request): SubscriptionInfoResource|Response
    {
        $workspace = Workspace::firstOrFailByUuid($request->route('workspace'));

        $subscription = $workspace->subscription();

        if (! $subscription) {
            return response()->noContent();
        }

        return new SubscriptionInfoResource($subscription->subscriptionInfo());
    }
}
