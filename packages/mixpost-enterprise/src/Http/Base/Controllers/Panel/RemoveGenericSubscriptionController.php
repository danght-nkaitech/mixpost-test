<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Models\Workspace;

class RemoveGenericSubscriptionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $workspace = Workspace::firstOrFailByUuid($request->route('workspace'));

        $workspace->saveLimits([]);
        $workspace->removeGenericSubscription();

        return redirect()->back()->with('success', __('mixpost-enterprise::subscription.generic_sub_removed'));
    }
}
