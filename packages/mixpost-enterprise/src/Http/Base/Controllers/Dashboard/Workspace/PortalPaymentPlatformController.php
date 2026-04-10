<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inovector\Mixpost\Facades\WorkspaceManager;

class PortalPaymentPlatformController extends Controller
{
    public function __invoke(Request $request): Response|\Symfony\Component\HttpFoundation\Response
    {
        $workspace = WorkspaceManager::current();

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
