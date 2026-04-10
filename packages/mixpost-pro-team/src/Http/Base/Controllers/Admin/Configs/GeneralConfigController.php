<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin\Configs;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Configs\GeneralConfig;
use Inovector\Mixpost\Facades\UrlShortenerManager;
use Inovector\Mixpost\Http\Base\Requests\Admin\Configs\SaveGeneralConfig;

class GeneralConfigController extends Controller
{
    public function form(): Response
    {
        return Inertia::render('Admin/Configs/GeneralConfig', [
            'configs' => (new GeneralConfig)->all(),
            'urlShortenerProviders' => UrlShortenerManager::getProviderSelectionOptionKeys(),
        ]);
    }

    public function update(SaveGeneralConfig $saveGeneralConfig): RedirectResponse
    {
        $saveGeneralConfig->handle();

        return redirect()->back();
    }
}
