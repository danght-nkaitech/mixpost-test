<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Main;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\MixpostEnterprise\Events\User\UserEmailVerified;
use Inovector\MixpostEnterprise\Models\Invitation;

class VerifyEmail extends EmailVerificationRequest
{
    use UsesUserModel;

    public function fulfill(): void
    {
        $user = self::getUserClass()::find($this->user()->id);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new UserEmailVerified($user));
        }
    }

    public function invitation(): ?Invitation
    {
        return Invitation::findByEmail($this->user()->email);
    }
}
