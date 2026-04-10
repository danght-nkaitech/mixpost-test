<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Http\Base\Requests\Auth\InstallationRequest;
use Inovector\Mixpost\Support\TimezoneList;

class InstallationController extends Controller
{
    use UsesUserModel;

    public function create(): Response|RedirectResponse
    {
        if (self::getUserClass()::exists()) {
            return redirect()->route('mixpost.login');
        }

        return Inertia::render('Auth/Installation', [
            'timezoneList' => (new TimezoneList)->splitGroup()->list(),
        ]);
    }

    public function store(InstallationRequest $request): RedirectResponse
    {
        $request->handle();

        return redirect()->route('mixpost.workspaces.create');
    }
}
