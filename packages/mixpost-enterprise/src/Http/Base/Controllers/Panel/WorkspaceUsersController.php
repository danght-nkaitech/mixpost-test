<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace\AttachWorkspaceUser;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace\DetachWorkspaceUser;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace\UpdateWorkspaceUserRole;

class WorkspaceUsersController extends Controller
{
    public function store(AttachWorkspaceUser $addWorkspaceUser): RedirectResponse
    {
        $addWorkspaceUser->handle();

        return redirect()->back();
    }

    public function update(UpdateWorkspaceUserRole $updateWorkspaceUserRole): RedirectResponse
    {
        $updateWorkspaceUserRole->handle();

        return redirect()->back();
    }

    public function destroy(DetachWorkspaceUser $detachWorkspaceUser): RedirectResponse
    {
        $detachWorkspaceUser->handle();

        return redirect()->back();
    }
}
