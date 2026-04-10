<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\UpdateInvitation;

class UpdateInvitationController extends Controller
{
    public function __invoke(UpdateInvitation $updateInvitation): RedirectResponse
    {
        $updateInvitation->handle();

        return redirect()->back();
    }
}
