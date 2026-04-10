<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Main;

use Illuminate\Http\RedirectResponse;
use Inovector\Mixpost\Http\Base\Requests\Main\ConfirmPassword;

class ConfirmPasswordController
{
    public function __invoke(ConfirmPassword $confirmPassword): RedirectResponse
    {
        $confirmPassword->handle();

        return redirect()->back();
    }
}
