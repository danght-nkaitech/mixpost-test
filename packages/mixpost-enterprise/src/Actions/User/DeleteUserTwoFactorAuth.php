<?php

namespace Inovector\MixpostEnterprise\Actions\User;

use Inovector\Mixpost\Abstracts\User;

class DeleteUserTwoFactorAuth
{
    public function __invoke(User $user): void
    {
        $user->twoFactorAuth()->delete();
    }
}
