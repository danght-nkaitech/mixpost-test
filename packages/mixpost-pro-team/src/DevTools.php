<?php

namespace Inovector\Mixpost;

use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Facades\WorkspaceManager;

class DevTools
{
    use UsesAuth;

    public static function ddIfUser(int $userId, mixed ...$vars): void
    {
        if (self::getAuthGuard()->id() === $userId) {
            dd($vars);
        }
    }

    public static function ddIfAdmin(mixed ...$vars): void
    {
        if (self::getAuthGuard()->user()->isAdmin()) {
            dd($vars);
        }
    }

    public static function ddIfWorkspaceAdmin(mixed ...$vars): void
    {
        if (WorkspaceManager::current() && self::getAuthGuard()
            ->user()
            ->hasWorkspace(
                WorkspaceManager::current(),
                WorkspaceUserRole::ADMIN
            )) {
            dd($vars);
        }
    }
}
