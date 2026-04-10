<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Subscription;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Subscription\DeleteSubscription;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Subscription\StoreSubscription;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Subscription\UpdateSubscription;
use Inovector\MixpostEnterprise\Http\Api\Resources\SubscriptionResource;

class SubscriptionController extends Controller
{
    use UsesWorkspaceModel;

    public function store(StoreSubscription $storeSubscription): JsonResponse
    {
        $storeSubscription->handle();

        return response()->json([
            'success' => true,
        ], 201);
    }

    public function show(Request $request): SubscriptionResource|JsonResponse
    {
        $workspace = self::getWorkspaceModelClass()::firstOrFailByUuid($request->route('workspace'));

        $subscription = $workspace->subscription();

        $subscription?->load(['planMonthly', 'planYearly']);

        if (! $subscription) {
            return response()->json(null);
        }

        return new SubscriptionResource($subscription);
    }

    public function update(UpdateSubscription $updateSubscription): JsonResponse
    {
        return response()->json([
            'success' => $updateSubscription->handle(),
        ]);
    }

    public function delete(DeleteSubscription $deleteSubscription): JsonResponse
    {
        return response()->json([
            'success' => $deleteSubscription->handle(),
        ]);
    }
}
