<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace\CancelSubscription;

class CancelSubscriptionController extends Controller
{
    public function __invoke(CancelSubscription $cancelSubscription): RedirectResponse
    {
        $cancelSubscription->handle();

        return redirect()->back()->with('success', __('mixpost-enterprise::subscription.sub_cancelled'));
    }
}
