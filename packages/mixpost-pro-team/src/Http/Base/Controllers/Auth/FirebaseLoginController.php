<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\FirebaseException;

class FirebaseLoginController extends Controller
{
    use UsesAuth;
    use UsesUserModel;

    public function __invoke(Request $request, FirebaseAuth $auth): RedirectResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        try {
            $verifiedToken = $auth->verifyIdToken($request->input('id_token'));
        } catch (FailedToVerifyToken|FirebaseException $e) {
            return redirect()->route('mixpost.login')->withErrors([
                'email' => __('The Firebase token is invalid or expired. Please try again.'),
            ]);
        }

        $claims = $verifiedToken->claims();
        $email = $claims->get('email');

        if (! $email) {
            return redirect()->route('mixpost.login')->withErrors([
                'email' => __('Your account has no email address associated.'),
            ]);
        }

        $name = $claims->get('name') ?? explode('@', $email)[0];

        $userClass = self::getUserClass();

        $user = $userClass::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(Str::random(32)),
            ]
        );

        // Firebase has already verified the email — ensure it's marked in our DB
        if (is_null($user->email_verified_at)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        self::getAuthGuard()->login($user, true);

        $request->session()->regenerate();

        return redirect()->intended(route('mixpost.home'));
    }
}
