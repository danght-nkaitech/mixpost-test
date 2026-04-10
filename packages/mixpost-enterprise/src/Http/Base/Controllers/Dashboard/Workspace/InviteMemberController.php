<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\InviteMember;

class InviteMemberController extends Controller
{
    public function __invoke(InviteMember $inviteMember): RedirectResponse
    {
        $inviteMember->handle();

        return redirect()->back();
    }
}
