<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Builders\User\CurrentWorkspaceUserQuery;
use Inovector\Mixpost\Http\Base\Resources\UserResource;

class UsersController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        CurrentWorkspaceUserQuery::apply($request);

        return UserResource::collection(CurrentWorkspaceUserQuery::apply($request)->get());
    }
}
