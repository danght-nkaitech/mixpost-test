<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Main\UpdateAuthUserPassword;

class UpdateAuthUserPasswordController extends Controller
{
    public function __invoke(UpdateAuthUserPassword $updateAuthUserPassword): RedirectResponse
    {
        $updateAuthUserPassword->handle();

        return back();
    }
}
