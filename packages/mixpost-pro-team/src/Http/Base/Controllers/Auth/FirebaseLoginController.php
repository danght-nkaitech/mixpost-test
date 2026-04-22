<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $firebaseUid = $claims->get('sub');

        Log::info('Firebase token verified', ['firebase_uid' => $firebaseUid, 'email' => $email]);

        if (! $email) {
            return redirect()->route('mixpost.login')->withErrors([
                'email' => __('Your account has no email address associated.'),
            ]);
        }

        $userClass = self::getUserClass();

        // Find existing account by firebase_uid first, then by email
        $user = $userClass::where('firebase_uid', $firebaseUid)->first()
            ?? $userClass::where('email', $email)->first();

        // Admin accounts bypass the Xroid check entirely
        if (! $user || ! $user->isAdmin()) {
            if (! $this->existsInXroid($request->input('id_token'))) {
                Log::warning('Firebase login rejected — Xroid check failed', ['email' => $email]);

                return redirect()->route('mixpost.login')->withErrors([
                    'email' => __('Your account does not exist in the system. Please contact an administrator.'),
                ]);
            }

            if (! $user) {
                // Exists in Xroid but not locally — create the account
                $name = $claims->get('name') ?? explode('@', $email)[0];

                $user = $userClass::create([
                    'name'               => $name,
                    'email'              => $email,
                    'password'           => Hash::make(Str::random(32)),
                    'firebase_uid'       => $firebaseUid,
                    'firebase_linked_at' => now(),
                ]);

                Log::info('New user created via Firebase + Xroid', ['email' => $email]);
            }
        }

        // Link Firebase UID to the local account if not already linked
        if (is_null($user->firebase_uid)) {
            $user->update([
                'firebase_uid'       => $firebaseUid,
                'firebase_linked_at' => now(),
            ]);
        }

        // Firebase has already verified the email — ensure it's marked in our DB
        if (is_null($user->email_verified_at)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        self::getAuthGuard()->login($user, true);

        $request->session()->regenerate();

        return redirect()->intended(route('mixpost.home'));
    }

    private function existsInXroid(string $accessToken): bool
    {
        $url = config('services.xroid.url');

        if (! $url) {
            Log::warning('Xroid API not configured — blocking unrecognized user');
            return false;
        }

        try {
            $response = Http::withToken($accessToken)
                ->timeout(5)
                ->get(rtrim($url, '/') . '/auth/info');
            Log::info('Xroid API /auth/info response', ['status' => $response->status(), 'body' => $response->body()]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Xroid /auth/info check failed', ['status' => $response->status()]);
        } catch (\Exception $e) {
            Log::error('Xroid API request error', ['error' => $e->getMessage()]);
        }

        return false;
    }
}
