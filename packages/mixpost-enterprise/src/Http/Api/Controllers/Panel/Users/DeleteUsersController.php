<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Users;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Concerns\UsesUserModel;

class DeleteUsersController extends Controller
{
    use UsesUserModel;

    public function __invoke(Request $request): JsonResponse
    {
        $ids = Arr::where($request->input('users'), function ($id) {
            return $id !== Auth::id();
        });

        return response()->json([
            'success' => self::getUserClass()::whereIn('id', $ids)->delete(),
        ]);
    }
}
