<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin\Configs;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Configs\MediaConfig;
use Inovector\Mixpost\Http\Base\Requests\Admin\Configs\SaveMediaConfig;

class MediaConfigController extends Controller
{
    public function form(): Response
    {
        return Inertia::render('Admin/Configs/MediaConfig', [
            'configs' => (new MediaConfig)->all(),
            'stockPhotoProviders' => ['unsplash', 'pexels'],
        ]);
    }

    public function update(SaveMediaConfig $mediaConfig): RedirectResponse
    {
        $mediaConfig->handle();

        return redirect()->back();
    }
}
