<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Builders\User\UserQuery;
use Inovector\Mixpost\Http\Base\Resources\UserResource;

class UserItemsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $users = UserQuery::apply($request)->latest()->latest('id')->get();

        return UserResource::collection($users);
    }
}
