<?php

namespace Inovector\MixpostEnterprise\Actions\User;

use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Facades\Settings;

class DeleteUserSettings
{
    public function __invoke(User $user): void
    {
        Settings::forgetAll($user->id);

        $user->settings()->delete();
    }
}
