<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace\AddGenericSubscription;

class AddGenericSubscriptionController extends Controller
{
    public function __invoke(AddGenericSubscription $addGenericSubscription): RedirectResponse
    {
        $addGenericSubscription->handle();

        return redirect()->back()->with('success', __('mixpost-enterprise::subscription.sub_added'));
    }
}
