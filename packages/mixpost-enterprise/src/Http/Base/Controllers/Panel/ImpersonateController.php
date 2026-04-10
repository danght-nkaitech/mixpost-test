<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\MixpostEnterprise\Facades\Impersonation;

class ImpersonateController extends Controller
{
    use UsesAuth;
    use UsesUserModel;

    public function startImpersonating(string $user): RedirectResponse
    {
        if (Impersonation::impersonating()) {
            return $this->stopImpersonating();
        }

        if (! Impersonation::canImpersonate()) {
            abort(403);
        }

        $user = self::getUserClass()::findOrFail($user);

        if (self::getAuthGuard()->id() === $user->id) {
            abort(403);
        }

        Impersonation::impersonate($user);

        return redirect()->route('mixpost.home');
    }

    public function stopImpersonating(): RedirectResponse
    {
        $impersonated = self::getAuthGuard()->user();

        Impersonation::stopImpersonating();

        if (! Impersonation::canImpersonate()) {
            return redirect()->route('mixpost.home');
        }

        return redirect()->route('mixpost_e.users.view', ['user' => $impersonated->id]);
    }
}
