<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace;

use Illuminate\Support\Facades\DB;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\Mixpost\Http\Base\Requests\Admin\DetachWorkspaceUser as CoreDetachWorkspaceUser;

class DetachWorkspaceUser extends CoreDetachWorkspaceUser
{
    use UsesWorkspaceModel;

    public function handle(): void
    {
        $workspace = self::getWorkspaceModelClass()::firstOrFailByUuid($this->route('workspace'));
        $user = self::getUserClass()::find($this->input('user_id'));

        DB::transaction(function () use ($workspace, $user) {
            if ($workspace->isOwner($user)) {
                $workspace->deleteOwner();
            }

            $workspace->users()->detach($this->input('user_id'));
        });
    }
}
