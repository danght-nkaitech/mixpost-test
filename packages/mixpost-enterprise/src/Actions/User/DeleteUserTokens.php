<?php

namespace Inovector\MixpostEnterprise\Actions\User;

use Inovector\Mixpost\Abstracts\User;

class DeleteUserTokens
{
    public function __invoke(User $user): void
    {
        $user->tokens()->delete();
    }
}
