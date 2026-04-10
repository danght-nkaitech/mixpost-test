<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inovector\MixpostEnterprise\Models\Workspace;

class PortalPaymentPlatformController extends Controller
{
    public function __invoke(Request $request): Response|\Symfony\Component\HttpFoundation\Response
    {
        $workspace = Workspace::firstOrFailByUuid($request->route('workspace'));

        $subscription = $workspace->subscription();

        if (! $subscription) {
            return response()->noContent();
        }

        $url = $subscription->portalUrl();

        if (! $url) {
            return back()->with('error', __('mixpost-enterprise::dashboard.unable_update_payment'));
        }

        return Inertia::location($url);
    }
}
