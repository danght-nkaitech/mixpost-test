<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\MixpostEnterprise\Configs\BillingConfig;
use Inovector\MixpostEnterprise\FeatureLimit;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Plan\StorePlan;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Plan\UpdatePlan;
use Inovector\MixpostEnterprise\Http\Base\Resources\PlanResource;
use Inovector\MixpostEnterprise\Models\Plan;

class PlansController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Panel/Plans/Plans', [
            'plans' => PlanResource::collection(Plan::ordered()->get()),
            'currency' => (new BillingConfig)->get('currency'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Panel/Plans/CreateEdit', [
            'mode' => 'create',
            'featureLimitResources' => FeatureLimit::list(),
            'currency' => (new BillingConfig)->get('currency'),
        ]);
    }

    public function store(StorePlan $storePlan): RedirectResponse
    {
        $plan = $storePlan->handle();

        return redirect()
            ->route('mixpost_e.plans.view', ['plan' => $plan->id])
            ->with('success', __('mixpost-enterprise::plan.plan_created'));
    }

    public function view(Request $request): Response
    {
        $plan = Plan::findOrFail($request->route('plan'));

        return Inertia::render('Panel/Plans/View', [
            'featureLimitResources' => FeatureLimit::list(),
            'plan' => new PlanResource($plan),
            'currency' => (new BillingConfig)->get('currency'),
        ]);
    }

    public function edit(Request $request): Response
    {
        $plan = Plan::findOrFail($request->route('plan'));

        return Inertia::render('Panel/Plans/CreateEdit', [
            'mode' => 'edit',
            'featureLimitResources' => FeatureLimit::list(),
            'plan' => new PlanResource($plan),
            'currency' => (new BillingConfig)->get('currency'),
        ]);
    }

    public function update(UpdatePlan $updatePlan): RedirectResponse
    {
        $updatePlan->handle();

        return redirect()
            ->back()
            ->with('success', __('mixpost-enterprise::plan.plan_updated'));
    }

    public function delete(Request $request): RedirectResponse
    {
        $plan = Plan::findOrFail($request->route('plan'));

        if ($plan->used()) {
            return redirect()
                ->back()
                ->with('error', __('mixpost-enterprise::subscription.plan_has_sub').__("\n").__('mixpost-enterprise::plan.can_disable_plan'));
        }

        $plan->delete();

        return redirect()
            ->route('mixpost_e.plans.index')
            ->with('success', __('mixpost-enterprise::plan.plan_deleted'));
    }
}
