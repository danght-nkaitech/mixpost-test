<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\DetachUser;

class DetachUserController extends Controller
{
    public function __invoke(DetachUser $detachUser): RedirectResponse
    {
        $detachUser->handle();

        return redirect()->back();
    }
}
