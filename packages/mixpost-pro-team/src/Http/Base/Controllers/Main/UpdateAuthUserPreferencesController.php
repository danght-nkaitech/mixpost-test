<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Main\SaveSettings;

class UpdateAuthUserPreferencesController extends Controller
{
    public function __invoke(SaveSettings $saveSettings): RedirectResponse
    {
        $saveSettings->handle();

        return back();
    }
}
