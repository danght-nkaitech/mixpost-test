<?php

namespace Inovector\Mixpost\Actions\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Support\LoginRateLimiter;

class RedirectIfTwoFactorAuthenticatable
{
    use UsesAuth;
    use UsesUserModel;

    public function __construct(protected readonly LoginRateLimiter $limiter) {}

    public function handle(Request $request, callable $next)
    {
        $user = $this->validateCredentials($request);

        if ($user->hasTwoFactorAuthEnabled()) {
            return $this->twoFactorChallengeResponse($request, $user);
        }

        return $next($request);
    }

    protected function validateCredentials($request)
    {
        return tap(self::getUserClass()::where('email', $request->input('email'))->first(), function ($user) use ($request) {
            if (! $user || ! self::getAuthGuard()->getProvider()->validateCredentials($user, ['password' => $request->password])) {
                $this->throwFailedAuthenticationException($request);
            }
        });
    }

    protected function throwFailedAuthenticationException($request)
    {
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    protected function twoFactorChallengeResponse(Request $request, $user): RedirectResponse
    {
        $request->session()->put([
            'login.id' => $user->getKey(),
            'login.remember' => $request->boolean('remember'),
        ]);

        return redirect()->route('mixpost.two-factor.login');
    }
}
