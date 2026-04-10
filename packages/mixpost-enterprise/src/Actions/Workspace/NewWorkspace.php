<?php

namespace Inovector\MixpostEnterprise\Actions\Workspace;

use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\MixpostEnterprise\Models\Workspace;

class NewWorkspace
{
    public function __invoke(User $user): Workspace
    {
        $workspace = Workspace::create([
            'name' => "$user->name's Team",
            'hex_color' => '000000',
        ]);

        $workspace->attachUser(
            id: $user->id,
            role: WorkspaceUserRole::ADMIN,
            canApprove: true
        );

        $workspace->saveOwner($user);

        return $workspace;
    }
}
