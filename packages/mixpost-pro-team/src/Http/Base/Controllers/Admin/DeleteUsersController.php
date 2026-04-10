<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Facades\Settings;

class DeleteUsersController extends Controller
{
    use UsesUserModel;

    public function __invoke(Request $request): RedirectResponse
    {
        $ids = Arr::where($request->input('users'), function ($id) {
            return $id !== Auth::id();
        });

        self::getUserClass()::whereIn('id', $ids)->delete();

        collect($ids)->each(function ($id) {
            Settings::forgetAll($id);
        });

        return redirect()->back();
    }
}
