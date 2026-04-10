<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Workspace;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Actions\Workspace\DestroyWorkspace;
use Inovector\MixpostEnterprise\Models\Workspace;

class DeleteWorkspacesController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Workspace::select(['id', 'uuid'])->whereIn('uuid', $request->input('workspaces', []))
            ->get()
            ->each(function ($workspace) {
                (new DestroyWorkspace)($workspace, true);
            });

        return response()->json([
            'success' => true,
        ]);
    }
}
