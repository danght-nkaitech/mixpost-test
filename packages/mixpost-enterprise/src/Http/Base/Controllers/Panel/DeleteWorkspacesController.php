<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Actions\Workspace\DestroyWorkspace;
use Inovector\MixpostEnterprise\Models\Workspace;

class DeleteWorkspacesController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        Workspace::select(['id', 'uuid'])->whereIn('uuid', $request->input('workspaces', []))
            ->get()
            ->each(function ($workspace) {
                (new DestroyWorkspace)($workspace, true);
            });

        return redirect()
            ->back()
            ->with('success', __('mixpost-enterprise::workspace.workspaces_deleted'));
    }
}
