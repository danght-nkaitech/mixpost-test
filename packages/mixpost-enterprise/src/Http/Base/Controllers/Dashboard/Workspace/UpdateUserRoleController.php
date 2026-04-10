<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\UpdateUserRole;

class UpdateUserRoleController extends Controller
{
    public function __invoke(UpdateUserRole $updateUserRole): RedirectResponse
    {
        $updateUserRole->handle();

        return redirect()->back();
    }
}
