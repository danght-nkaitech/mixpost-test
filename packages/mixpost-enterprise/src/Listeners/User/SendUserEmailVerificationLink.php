<?php

namespace Inovector\MixpostEnterprise\Listeners\User;

use Illuminate\Support\Facades\App;
use Inovector\MixpostEnterprise\Configs\OnboardingConfig;
use Inovector\MixpostEnterprise\Events\User\UserCreated;
use Inovector\MixpostEnterprise\Notifications\VerifyEmail;

class SendUserEmailVerificationLink
{
    public function handle(UserCreated $event): void
    {
        if (! app(OnboardingConfig::class)->get('email_verification')) {
            return;
        }

        if ($event->fromAdmin) {
            return;
        }

        $event->user->notify((new VerifyEmail)->locale(App::getLocale()));
    }
}
