<?php

namespace Inovector\Mixpost\Concerns;

use Inovector\Mixpost\Models\User;
use Inovector\Mixpost\Util;

trait UsesUserModel
{
    public static function getUserClass(): string
    {
        return Util::config('user_model', User::class);
    }
}
