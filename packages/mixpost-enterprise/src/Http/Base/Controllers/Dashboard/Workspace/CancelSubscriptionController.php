<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\CancelSubscription;

class CancelSubscriptionController extends Controller
{
    public function __invoke(CancelSubscription $cancelSubscription): RedirectResponse
    {
        $cancelSubscription->handle();

        return redirect()->back()->with('success', __('mixpost-enterprise::subscription.sub_cancelled'));
    }
}
