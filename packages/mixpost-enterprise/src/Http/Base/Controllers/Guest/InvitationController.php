<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Guest;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Models\Invitation;

class InvitationController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $invitation = Invitation::firstOrFailByUuid($request->route('invitation'));

        if (! $invitation->user && ! $invitation->userByEmail) {
            return redirect()->route('mixpost_e.register', ['email' => $invitation->email]);
        }

        return redirect()->route('mixpost_e.invitations.view', ['invitation' => $invitation->uuid]);
    }
}
