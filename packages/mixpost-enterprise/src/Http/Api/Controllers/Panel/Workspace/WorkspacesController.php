<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Support\AnonymousResourceCollectionWithParameters;
use Inovector\MixpostEnterprise\Actions\Workspace\DestroyWorkspace;
use Inovector\MixpostEnterprise\Builders\Workspace\WorkspaceQuery;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\StoreWorkspace;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\UpdateWorkspace;
use Inovector\MixpostEnterprise\Http\Api\Resources\SubscriptionResource;
use Inovector\MixpostEnterprise\Http\Api\Resources\WorkspaceResource;
use Inovector\MixpostEnterprise\Models\Workspace;

class WorkspacesController extends Controller
{
    public function index(Request $request): AnonymousResourceCollectionWithParameters
    {
        $workspaces = WorkspaceQuery::apply($request)
            ->with(['owner', 'genericSubscriptionPlan'])
            ->with(['subscriptions' => function ($query) {
                $query->with(['planMonthly', 'planYearly']);
            }])
            ->latest()
            ->paginate(20);

        return WorkspaceResource::collection($workspaces);
    }

    public function findByEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $workspaces = WorkspaceQuery::apply($request)
            ->with(['owner'])
            ->latest()
            ->get();

        return response()->json([
            'data' => new WorkspaceResource($workspaces->first()),
        ]);
    }

    public function store(StoreWorkspace $storeWorkspace): WorkspaceResource
    {
        return new WorkspaceResource(
            $storeWorkspace->handle()
        );
    }

    public function show(Request $request): JsonResponse
    {
        $workspace = Workspace::firstOrFailByUuid($request->route('workspace'))
            ->load(['owner', 'users', 'genericSubscriptionPlan']);

        $subscription = $workspace->subscription();

        $subscription?->load(['planMonthly', 'planYearly']);

        return response()->json([
            'workspace' => (new WorkspaceResource($workspace))->additionalFields([
                'payment_method' => [
                    'type' => $workspace->pm_type,
                    'card_brand' => $workspace->pm_card_brand,
                    'card_last_four' => $workspace->pm_card_last_four,
                    'card_expires' => $workspace->pm_card_expires,
                ],
            ]),
            'subscription' => $subscription ? new SubscriptionResource($subscription) : null,
        ]);
    }

    public function update(UpdateWorkspace $updateWorkspace): JsonResponse
    {
        return response()->json([
            'success' => $updateWorkspace->handle(),
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $workspace = Workspace::firstOrFailByUuid($request->route('workspace'));

        (new DestroyWorkspace)($workspace);

        return response()->json([
            'success' => true,
        ]);
    }
}
