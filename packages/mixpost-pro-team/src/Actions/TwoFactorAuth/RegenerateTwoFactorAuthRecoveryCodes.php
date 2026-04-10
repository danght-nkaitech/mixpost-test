<?php

namespace Inovector\Mixpost\Actions\TwoFactorAuth;

use Illuminate\Support\Collection;
use Inovector\Mixpost\Support\RecoveryCode;

class RegenerateTwoFactorAuthRecoveryCodes
{
    public function __invoke($user): void
    {
        $user->twoFactorAuth->update([
            'recovery_codes' => Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all(),
        ]);
    }
}
