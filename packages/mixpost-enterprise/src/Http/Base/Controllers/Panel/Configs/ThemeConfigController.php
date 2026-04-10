<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\Configs;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\MixpostEnterprise\Facades\Theme;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Configs\SaveThemeConfig;

class ThemeConfigController extends Controller
{
    public function view(Request $request): Response
    {
        return Inertia::render('Panel/Configs/ThemeConfig', [
            'configs' => Theme::config()->all(),
            'primaryColors' => Arr::map(Theme::getDefault('primary'), function ($_, $color) {
                return [
                    'key' => $color,
                    'name' => Str::title($color),
                ];
            }),
        ]);
    }

    public function update(SaveThemeConfig $saveThemeConfig): \Symfony\Component\HttpFoundation\Response
    {
        $saveThemeConfig->handle();

        return Inertia::location(route('mixpost_e.configs.theme.view'));
    }
}
