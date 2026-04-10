<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Main\DeclineInvitation;

class DeclineInvitationController extends Controller
{
    public function __invoke(DeclineInvitation $cancelInvitation): RedirectResponse
    {
        $cancelInvitation->handle();

        return redirect()->route('mixpost.home');
    }
}
