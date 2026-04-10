<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\UpdateAccountSuffix;

class UpdateAccountSuffixController extends Controller
{
    public function __invoke(UpdateAccountSuffix $updateAccountSuffix): RedirectResponse
    {
        $updateAccountSuffix->handle();

        return redirect()->back();
    }
}
