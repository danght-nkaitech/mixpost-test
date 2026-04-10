<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Inovector\MixpostEnterprise\Configs\OnboardingConfig;
use Inovector\MixpostEnterprise\Notifications\VerifyEmail;

class ResendEmailVerificationController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        if (! app(OnboardingConfig::class)->get('email_verification') || Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('mixpost.home');
        }

        Auth::user()->notify((new VerifyEmail)->locale(App::getLocale()));

        return redirect()->back()->with('success', __('mixpost-enterprise::onboarding.email_verification_sent'));
    }
}
