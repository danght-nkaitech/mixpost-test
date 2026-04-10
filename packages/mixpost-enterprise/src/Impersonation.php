<?php

namespace Inovector\MixpostEnterprise;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Concerns\UsesUserModel;

class Impersonation
{
    use UsesAuth;
    use UsesUserModel;

    const SESSION_IMPERSONATED_BY = 'mixpost_impersonated_by';

    const SESSION_IMPERSONATED_REMEMBER = 'mixpost_impersonated_remember';

    public function __construct(public readonly Request $request) {}

    public function impersonate(Authenticatable $user): void
    {
        $impersonator = self::getAuthGuard()->user();

        $this->request->session()->put(
            self::SESSION_IMPERSONATED_BY, $impersonator->getAuthIdentifier()
        );

        $this->request->session()->put(
            self::SESSION_IMPERSONATED_REMEMBER, self::getAuthGuard()->viaRemember()
        );

        self::getAuthGuard()->login($user);
    }

    public function stopImpersonating(): void
    {
        if (! $this->impersonating()) {
            return;
        }

        $impersonator = self::getUserClass()::find($this->request->session()->get(self::SESSION_IMPERSONATED_BY));

        self::getAuthGuard()->logout();

        if (! $impersonator) {
            return;
        }

        self::getAuthGuard()->login($impersonator, $this->request->session()->get(self::SESSION_IMPERSONATED_REMEMBER));

        $this->flushImpersonationData();
    }

    public function canImpersonate(): bool
    {
        if (! self::getAuthGuard()->check()) {
            return false;
        }

        /** @var \App\Models\User $user */
        $user = self::getAuthGuard()->user();

        return $user->isAdmin();
    }

    public function impersonating(): bool
    {
        return $this->request->session()->has(self::SESSION_IMPERSONATED_BY);
    }

    public function flushImpersonationData(): void
    {
        if ($this->request->hasSession()) {
            $this->request->session()->forget(self::SESSION_IMPERSONATED_BY);
            $this->request->session()->forget(self::SESSION_IMPERSONATED_REMEMBER);
        }
    }
}
