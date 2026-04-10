<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\Configs;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\MixpostEnterprise\Facades\ScriptsConfig;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Configs\SaveScriptsConfig;

class ScriptsConfigController extends Controller
{
    public function view(): Response
    {
        return Inertia::render('Panel/Configs/ScriptsConfig', [
            'configs' => ScriptsConfig::all(),
        ]);
    }

    public function update(SaveScriptsConfig $saveScriptsConfig): RedirectResponse
    {
        $saveScriptsConfig->handle();

        return redirect()->back();
    }
}
