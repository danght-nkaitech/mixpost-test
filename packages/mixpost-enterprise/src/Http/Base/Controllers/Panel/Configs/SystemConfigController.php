<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\Configs;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\MixpostEnterprise\Configs\SystemConfig;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Configs\SaveSystemConfig;

class SystemConfigController extends Controller
{
    public function view(): Response
    {
        return Inertia::render('Panel/Configs/SystemConfig', [
            'configs' => app(SystemConfig::class)->all(),
        ]);
    }

    public function update(SaveSystemConfig $saveSystemConfig): RedirectResponse
    {
        $saveSystemConfig->handle();

        return redirect()->back();
    }
}
