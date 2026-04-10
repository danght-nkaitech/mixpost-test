<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\Configs;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\MixpostEnterprise\Configs\OnboardingConfig;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Configs\SaveOnboardingConfig;

class OnboardingConfigController extends Controller
{
    public function view(): Response
    {
        return Inertia::render('Panel/Configs/OnboardingConfig', [
            'configs' => app(OnboardingConfig::class)->all(),
        ]);
    }

    public function update(SaveOnboardingConfig $saveOnboardingConfig): RedirectResponse
    {
        $saveOnboardingConfig->handle();

        return redirect()->back();
    }
}
