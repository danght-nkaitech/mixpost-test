<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Admin\AttachWorkspaceUser;
use Inovector\Mixpost\Http\Base\Requests\Admin\DetachWorkspaceUser;
use Inovector\Mixpost\Http\Base\Requests\Admin\UpdateWorkspaceUserRole;

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
