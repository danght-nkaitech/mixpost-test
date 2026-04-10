<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin\Configs;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Configs\GifConfig;
use Inovector\Mixpost\Http\Base\Requests\Admin\Configs\SaveGifConfig;

class GifConfigController extends Controller
{
    public function form(): Response
    {
        return Inertia::render('Admin/Configs/GifConfig', [
            'configs' => (new GifConfig)->all(),
            'gifProviders' => ['tenor', 'giphy'],
        ]);
    }

    public function update(SaveGifConfig $gifConfig): RedirectResponse
    {
        $gifConfig->handle();

        return redirect()->back();
    }
}
