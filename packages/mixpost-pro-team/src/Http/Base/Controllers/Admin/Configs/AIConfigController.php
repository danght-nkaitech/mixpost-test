<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin\Configs;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Configs\AIConfig;
use Inovector\Mixpost\Facades\AIManager;
use Inovector\Mixpost\Http\Base\Requests\Admin\Configs\SaveAIConfig;

class AIConfigController extends Controller
{
    public function form(): Response
    {
        return Inertia::render('Admin/Configs/AIConfig', [
            'configs' => (new AIConfig)->all(),
            'providers' => AIManager::getProviderSelectionOptions(),
            'isConfigured' => AIManager::isAnyServiceActive(),
        ]);
    }

    public function update(SaveAIConfig $saveAIConfig): RedirectResponse
    {
        $saveAIConfig->handle();

        return redirect()->back();
    }
}
