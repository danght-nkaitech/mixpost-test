<?php

namespace Inovector\MixpostEnterprise\Actions\User;

use Inovector\Mixpost\Abstracts\User;
use Inovector\MixpostEnterprise\Actions\Workspace\DestroyWorkspace;

class DeleteUserOwnedWorkspaces
{
    public function __invoke(User $user): void
    {
        $user->workspaces()
            ->where('owner_id', $user->id)
            ->get()
            ->each(function ($workspace) {
                (new DestroyWorkspace)($workspace, true);
            });
    }
}
