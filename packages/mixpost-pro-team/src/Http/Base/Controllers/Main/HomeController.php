<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Mixpost;

class HomeController extends Controller
{
    use UsesUserModel;

    public function __invoke(): RedirectResponse
    {
        $workspace = Auth::user()->getActiveWorkspace();

        if (! $workspace) {
            $workspace = Auth::user()->workspaces()->recentlyUpdated()->first();

            // If there is a recently updated workspace, set it as user active workspace
            if ($workspace) {
                Auth::user()->setActiveWorkspace($workspace);
            }
        }

        if (! $workspace) {
            if (Auth::user()->isAdmin()) {
                return redirect()->to(route('mixpost.workspaces.create'));
            }

            if ($createWorkspaceUrl = Mixpost::getCreateWorkspaceUrl()) {
                return redirect()->to($createWorkspaceUrl);
            }

            abort(403);
        }

        return redirect()->to(route('mixpost.dashboard', ['workspace' => $workspace->uuid]));
    }
}
