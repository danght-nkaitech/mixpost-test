<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Builders\Workspace\WorkspaceQuery;
use Inovector\MixpostEnterprise\Http\Base\Resources\WorkspaceResource;

class WorkspaceItemsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $workspaces = WorkspaceQuery::apply($request)->latest()->get();

        return WorkspaceResource::collection($workspaces);
    }
}
