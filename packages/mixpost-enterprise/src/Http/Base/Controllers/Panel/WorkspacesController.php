<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\MixpostEnterprise\Actions\Workspace\DestroyWorkspace;
use Inovector\MixpostEnterprise\Builders\Workspace\WorkspaceQuery;
use Inovector\MixpostEnterprise\Configs\BillingConfig;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace\StoreWorkspace;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace\UpdateWorkspace;
use Inovector\MixpostEnterprise\Http\Base\Resources\PlanResource;
use Inovector\MixpostEnterprise\Http\Base\Resources\SubscriptionResource;
use Inovector\MixpostEnterprise\Http\Base\Resources\WorkspaceResource;
use Inovector\MixpostEnterprise\Models\Plan;
use Inovector\MixpostEnterprise\Models\Workspace;

class WorkspacesController extends Controller
{
    public function index(Request $request): Response
    {
        $workspaces = WorkspaceQuery::apply($request)
            ->with(['owner', 'genericSubscriptionPlan'])
            ->with(['subscriptions' => function ($query) {
                $query->with(['planMonthly', 'planYearly']);
            }])
            ->latest()
            ->paginate(20)
            ->onEachSide(1)
            ->withQueryString();

        return Inertia::render('Panel/Workspaces/Workspaces', [
            'workspaces' => WorkspaceResource::collection($workspaces),
            'filter' => [
                'keyword' => $request->query('keyword', ''),
                'subscription_status' => $request->query('subscription_status'),
                'free' => $request->query('free'),
                'access_status' => $request->query('access_status', []),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Panel/Workspaces/CreateEdit', [
            'mode' => 'create',
        ]);
    }

    public function store(StoreWorkspace $storeWorkspace): RedirectResponse
    {
        $workspace = $storeWorkspace->handle();

        return redirect()
            ->route('mixpost_e.workspaces.view', ['workspace' => $workspace->uuid]);
    }

    public function view(Request $request): Response
    {
        $workspace = Workspace::firstOrFailByUuid($request->route('workspace'))
            ->load(['owner', 'users', 'genericSubscriptionPlan']);

        $subscription = $workspace->subscription();

        $subscription?->load(['planMonthly', 'planYearly']);

        return Inertia::render('Panel/Workspaces/View', [
            'workspace' => (new WorkspaceResource($workspace))->additionalFields([
                'payment_method' => [
                    'type' => $workspace->pm_type,
                    'card_brand' => $workspace->pm_card_brand,
                    'card_last_four' => $workspace->pm_card_last_four,
                    'card_expires' => $workspace->pm_card_expires,
                ],
            ]),
            'subscription' => $subscription ? new SubscriptionResource($subscription) : null,
            'billingConfigs' => (new BillingConfig)->all(),
            'plans' => PlanResource::collection(Plan::get())->resolve(),
        ]);
    }

    public function update(UpdateWorkspace $updateWorkspace): RedirectResponse
    {
        $updateWorkspace->handle();

        return redirect()
            ->back()
            ->with('success', __('mixpost-enterprise::workspace.workspace_updated'));
    }

    public function edit(Request $request): Response
    {
        $workspace = Workspace::firstOrFailByUuid($request->route('workspace'))
            ->load('owner');

        return Inertia::render('Panel/Workspaces/CreateEdit', [
            'mode' => 'edit',
            'workspace' => new WorkspaceResource($workspace),
        ]);
    }

    public function delete(Request $request): RedirectResponse
    {
        $workspace = Workspace::firstOrFailByUuid($request->route('workspace'));

        (new DestroyWorkspace)($workspace);

        return redirect()
            ->route('mixpost_e.workspaces.index')
            ->with('success', __('mixpost-enterprise::workspace.workspace_deleted'));
    }
}
