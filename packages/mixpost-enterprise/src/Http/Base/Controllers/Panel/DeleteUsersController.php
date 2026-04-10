<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Concerns\UsesUserModel;

class DeleteUsersController extends Controller
{
    use UsesAuth;
    use UsesUserModel;

    public function __invoke(Request $request): RedirectResponse
    {
        $ids = Arr::where($request->input('users'), function ($id) {
            return $id !== self::getAuthGuard()->id();
        });

        self::getUserClass()::whereIn('id', $ids)->delete();

        return redirect()->back();
    }
}
