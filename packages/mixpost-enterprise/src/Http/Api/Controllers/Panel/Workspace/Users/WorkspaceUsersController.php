<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace\Users;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Users\AttachWorkspaceUser;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Users\DetachWorkspaceUser;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Workspace\Users\UpdateWorkspaceUserRole;

class WorkspaceUsersController extends Controller
{
    public function store(AttachWorkspaceUser $addWorkspaceUser): JsonResponse
    {
        $addWorkspaceUser->handle();

        return response()->json([
            'success' => true,
        ]);
    }

    public function update(UpdateWorkspaceUserRole $updateWorkspaceUserRole): JsonResponse
    {
        $updateWorkspaceUserRole->handle();

        return response()->json([
            'success' => true,
        ]);
    }

    public function destroy(DetachWorkspaceUser $detachWorkspaceUser): JsonResponse
    {
        $detachWorkspaceUser->handle();

        return response()->json([
            'success' => true,
        ]);
    }
}
