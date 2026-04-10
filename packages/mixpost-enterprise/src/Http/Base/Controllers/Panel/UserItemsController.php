<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Builders\User\UserQuery;
use Inovector\MixpostEnterprise\Http\Base\Resources\UserResource;

class UserItemsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $users = UserQuery::apply($request)->latest()->get();

        return UserResource::collection($users);
    }
}
