<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Main\AcceptInvitation;

class AcceptInvitationController extends Controller
{
    public function __invoke(AcceptInvitation $acceptInvitation): RedirectResponse
    {
        $acceptInvitation->handle();

        return redirect()->route('mixpost.dashboard', ['workspace' => $acceptInvitation->invitation()->workspace->uuid]);
    }
}
