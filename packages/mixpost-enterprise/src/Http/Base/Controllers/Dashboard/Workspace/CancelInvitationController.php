<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\CancelInvitation;

class CancelInvitationController extends Controller
{
    public function __invoke(CancelInvitation $cancelInvitation): RedirectResponse
    {
        $result = $cancelInvitation->handle();

        if (! $result) {
            return redirect()->back()->with('error', __('mixpost-enterprise::team.unable_cancel_invitation'));
        }

        return redirect()->back()->with('success', __('mixpost-enterprise::team.invitation_canceled'));
    }
}
