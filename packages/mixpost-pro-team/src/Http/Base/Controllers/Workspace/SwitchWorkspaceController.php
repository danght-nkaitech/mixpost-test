<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Facades\WorkspaceManager;

class SwitchWorkspaceController
{
    public function __invoke(Request $request)
    {
        Auth::user()->setActiveWorkspace(WorkspaceManager::current());

        if ($request->has('redirect')) {
            return redirect()->route('mixpost.dashboard', ['workspace' => WorkspaceManager::current()->uuid]);
        }
    }
}
