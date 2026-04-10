<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Builders\Workspace\WorkspaceQuery;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Http\Base\Requests\Admin\StoreWorkspace;
use Inovector\Mixpost\Http\Base\Requests\Admin\UpdateWorkspace;
use Inovector\Mixpost\Http\Base\Resources\WorkspaceResource;
use Inovector\Mixpost\Jobs\DeleteWorkspaceDataJob;
use Inovector\Mixpost\Models\Workspace;

class WorkspacesController extends Controller
{
    use UsesUserModel;

    public function index(Request $request): Response
    {
        $workspaces = WorkspaceQuery::apply($request)
            ->with('users')
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->onEachSide(1)
            ->withQueryString();

        return Inertia::render('Admin/Workspaces/Workspaces', [
            'workspaces' => WorkspaceResource::collection($workspaces),
            'filter' => [
                'keyword' => $request->query('keyword', ''),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Workspaces/CreateEdit', [
            'mode' => 'create',
        ]);
    }

    public function store(StoreWorkspace $storeWorkspace): RedirectResponse
    {
        $workspace = $storeWorkspace->handle();

        if (! $storeWorkspace->input('login')) {
            return redirect()->route('mixpost.workspaces.view', ['workspace' => $workspace->uuid]);
        }

        Auth::user()->setActiveWorkspace($workspace);

        return redirect()->route('mixpost.dashboard', ['workspace' => $workspace->uuid]);
    }

    public function view(Request $request): Response
    {
        $workspace = Workspace::firstOrFailByUuid($request->route('workspace'));
        $workspace->load('users');

        return Inertia::render('Admin/Workspaces/View', [
            'workspace' => new WorkspaceResource($workspace),
        ]);
    }

    public function update(UpdateWorkspace $updateWorkspace): RedirectResponse
    {
        $updateWorkspace->handle();

        return redirect()->back();
    }

    public function edit(Request $request): Response
    {
        $workspace = Workspace::firstOrFailByUuid($request->route('workspace'));
        $workspace->load('users');

        return Inertia::render('Admin/Workspaces/CreateEdit', [
            'mode' => 'edit',
            'workspace' => new WorkspaceResource($workspace),
        ]);
    }

    public function delete(Workspace $workspace): RedirectResponse
    {
        $workspace->delete();

        DeleteWorkspaceDataJob::dispatch($workspace->id, $workspace->uuid);

        return redirect()->route('mixpost.workspaces.index');
    }
}
