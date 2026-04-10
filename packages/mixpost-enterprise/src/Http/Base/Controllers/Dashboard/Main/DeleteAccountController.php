<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Main\DeleteAccount;

class DeleteAccountController extends Controller
{
    use UsesAuth;

    public function __invoke(DeleteAccount $request): RedirectResponse
    {
        $request->handle();

        self::getAuthGuard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('mixpost.login');
    }
}
