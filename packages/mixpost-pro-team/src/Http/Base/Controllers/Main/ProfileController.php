<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Main;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Features;
use Inovector\Mixpost\Mixpost;
use Inovector\Mixpost\Support\TimezoneList;
use Inovector\Mixpost\Util;

class ProfileController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Main/Profile', [
            'settings' => Settings::all(),
            'locales' => Util::config('locales'),
            'timezoneList' => (new TimezoneList)->splitGroup()->list(),
            'userHasTwoFactorAuthEnabled' => Auth::user()->hasTwoFactorAuthEnabled(),
            'is_two_factor_auth_enabled' => Features::isTwoFactorAuthEnabled(),
            'isDeleteAccountEnabled' => Features::isDeleteAccountEnabled(),
            'delete_account_url' => Mixpost::getDeleteAccountRoute() ? route(Mixpost::getDeleteAccountRoute()) : null,
        ]);
    }
}
