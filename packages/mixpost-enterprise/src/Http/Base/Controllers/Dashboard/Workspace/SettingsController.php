<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Configs\SystemConfig;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\UpdateWorkspace;
use Inovector\MixpostEnterprise\Http\Base\Resources\WorkspaceResource;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Workspace/Settings', [
            'workspace' => new WorkspaceResource(WorkspaceManager::current()),
            'allow_workspace_service' => [
                'twitter' => app(SystemConfig::class)->allowWorkspaceTwitterService(),
            ],
        ]);
    }

    public function update(UpdateWorkspace $updateWorkspace): RedirectResponse
    {
        $updateWorkspace->handle();

        return redirect()->back();
    }
}
