<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Http\Base\Requests\Auth\ResetPassword;

class NewPasswordController
{
    use UsesUserModel;

    public function create(Request $request): Response|RedirectResponse
    {
        if (! self::getUserClass()::exists()) {
            return redirect()->route('mixpost.installation');
        }

        return Inertia::render('Auth/ResetPassword', [
            'token' => $request->route('token'),
        ]);
    }

    public function store(ResetPassword $resetPassword): RedirectResponse
    {
        $status = $resetPassword->handle();

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('mixpost.login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
