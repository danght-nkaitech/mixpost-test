<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Main\VerifyEmail;

class VerifyEmailController extends Controller
{
    public function __invoke(VerifyEmail $emailVerification): RedirectResponse
    {
        $emailVerification->fulfill();

        if ($invitation = $emailVerification->invitation()) {
            return redirect()->route('mixpost_e.invitations.view', ['invitation' => $invitation->uuid]);
        }

        return redirect()->route('mixpost.home');
    }
}
