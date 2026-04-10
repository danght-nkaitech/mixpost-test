<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\DestroyWorkspace;
use Inovector\MixpostEnterprise\Http\Base\Resources\WorkspaceResource;

class SecurityController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Workspace/Security', [
            'workspace' => new WorkspaceResource(WorkspaceManager::current()),
        ]);
    }

    public function destroy(DestroyWorkspace $destroyWorkspace): RedirectResponse
    {
        $destroyWorkspace->handle();

        return redirect()->route('mixpost.home');
    }
}
