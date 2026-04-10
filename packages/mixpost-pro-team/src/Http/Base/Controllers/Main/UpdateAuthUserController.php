<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Main\UpdateAuthUser;

class UpdateAuthUserController extends Controller
{
    public function __invoke(UpdateAuthUser $updateUser): RedirectResponse
    {
        $updateUser->handle();

        return back();
    }
}
