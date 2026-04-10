<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\MixpostEnterprise\Builders\User\UserQuery;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\User\StoreUser;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\User\UpdateUser;
use Inovector\MixpostEnterprise\Http\Api\Resources\UserResource;

class UsersController extends Controller
{
    use UsesUserModel;

    public function index(Request $request): AnonymousResourceCollection
    {
        $users = UserQuery::apply($request)->with(['admin', 'settings'])->latest()->paginate(20);

        return UserResource::collection($users);
    }

    public function store(StoreUser $storeUser): UserResource
    {
        $user = $storeUser->handle();

        return new UserResource($user);
    }

    public function show(Request $request): UserResource
    {
        $user = self::getUserClass()::with('admin', 'workspaces')->findOrFail($request->route('user'));

        return new UserResource($user);
    }

    public function update(UpdateUser $updateUser): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => $updateUser->handle(),
        ]);
    }

    public function delete(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = self::getUserClass()::findOrFail($request->route('user'));

        if ($user->id !== Auth::id()) {
            self::getUserClass()::findOrFail($request->route('user'))->delete();

            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('mixpost-enterprise::dashboard.cant_delete'),
        ]);
    }
}
