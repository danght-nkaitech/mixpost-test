<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Http\Base\Resources\InvitationResource;
use Inovector\MixpostEnterprise\Http\Base\Resources\UserResource;
use Inovector\MixpostEnterprise\Http\Base\Resources\WorkspaceResource;

class MembersController extends Controller
{
    public function __invoke(): Response
    {
        $workspace = WorkspaceManager::current();

        return Inertia::render('Dashboard/Workspace/Members', [
            'workspace' => new WorkspaceResource($workspace),
            'users' => UserResource::collection($workspace->users()->latest()->get())->resolve(), // TODO: order by pivot `joined_at`
            'invitations' => InvitationResource::collection($workspace->invitations()->with('author')->latest()->get())->resolve(),
        ]);
    }
}
