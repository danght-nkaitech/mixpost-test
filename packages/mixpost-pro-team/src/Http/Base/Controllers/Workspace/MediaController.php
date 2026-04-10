<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Configs\GifConfig;
use Inovector\Mixpost\Configs\MediaConfig;
use Inovector\Mixpost\Enums\ServiceGroup;
use Inovector\Mixpost\Facades\ServiceManager;
use Inovector\Mixpost\Http\Base\Requests\Workspace\DeleteMedia;
use Inovector\Mixpost\Http\Base\Requests\Workspace\UpdateMedia;

class MediaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Workspace/Media', [
            'is_configured_service' => ServiceManager::isActive(
                ServiceManager::services()->group(ServiceGroup::MEDIA)->getNames()
            ),
            'stock_photo_provider' => app(MediaConfig::class)->get('stock_photo_provider'),
            'gif_provider' => app(GifConfig::class)->get('gif_provider'),
            'service_configs' => ServiceManager::exposedConfiguration(),
        ]);
    }

    public function update(UpdateMedia $updateMedia): RedirectResponse
    {
        $updateMedia->handle();

        return redirect()->back();
    }

    public function destroy(DeleteMedia $deleteMediaFiles): HttpResponse
    {
        $deleteMediaFiles->handle();

        return response()->noContent();
    }
}
