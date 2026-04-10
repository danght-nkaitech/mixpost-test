<?php

namespace Inovector\Mixpost\Actions\TwoFactorAuth;

use Illuminate\Support\Collection;
use Inovector\Mixpost\Support\RecoveryCode;
use Inovector\Mixpost\TwoFactorAuthProvider;

class EnableTwoFactorAuth
{
    public function __construct(private readonly TwoFactorAuthProvider $provider) {}

    public function __invoke($user): void
    {
        $data = [
            'secret_key' => $this->provider->generateSecretKey(),
            'recovery_codes' => Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all(),
        ];

        if ($twoFactorAuth = $user->twoFactorAuth) {
            $twoFactorAuth->update($data);
        } else {
            $user->twoFactorAuth()->create($data);
        }
    }
}
