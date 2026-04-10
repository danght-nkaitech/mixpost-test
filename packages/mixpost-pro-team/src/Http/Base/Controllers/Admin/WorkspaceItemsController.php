<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Builders\Workspace\WorkspaceQuery;
use Inovector\Mixpost\Http\Base\Resources\WorkspaceResource;

class WorkspaceItemsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $workspaces = WorkspaceQuery::apply($request)->latest()->latest('id')->get();

        return WorkspaceResource::collection($workspaces);
    }
}
