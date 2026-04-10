<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Pipeline;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Actions\Auth\AttemptToAuthenticate;
use Inovector\Mixpost\Actions\Auth\EnsureLoginIsNotThrottled;
use Inovector\Mixpost\Actions\Auth\PrepareAuthenticatedSession;
use Inovector\Mixpost\Actions\Auth\RedirectIfTwoFactorAuthenticatable;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Features;
use Inovector\Mixpost\Http\Base\Requests\Auth\LoginRequest;
use Inovector\Mixpost\Util;

class AuthenticatedController extends Controller
{
    use UsesAuth;
    use UsesUserModel;

    public function create(): Response|RedirectResponse
    {
        if (! self::getUserClass()::exists()) {
            return redirect()->route('mixpost.installation');
        }

        return Inertia::render('Auth/Login', [
            'locales' => Util::config('locales'),
            'isForgotPasswordEnabled' => Features::isForgotPasswordEnabled(),
            'firebaseConfig' => [
                'apiKey' => config('services.firebase_client.api_key'),
                'authDomain' => config('services.firebase_client.auth_domain'),
                'projectId' => config('services.firebase_client.project_id'),
                'appId' => config('services.firebase_client.app_id'),
            ],
        ]);
    }

    public function store(LoginRequest $request)
    {
        return (new Pipeline(app()))->send($request)->through(array_filter([
            EnsureLoginIsNotThrottled::class,
            Features::isTwoFactorAuthEnabled() ? RedirectIfTwoFactorAuthenticatable::class : null,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]))->then(function () {
            return redirect()->intended(route('mixpost.home'));
        });
    }

    public function destroy(Request $request): RedirectResponse
    {
        self::getAuthGuard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('mixpost.login');
    }
}
