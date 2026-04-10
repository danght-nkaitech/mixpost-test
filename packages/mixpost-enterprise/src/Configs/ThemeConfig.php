<?php

namespace Inovector\MixpostEnterprise\Configs;

use Illuminate\Support\Arr;
use Inovector\Mixpost\Abstracts\Config;
use Inovector\MixpostEnterprise\Facades\Theme;

class ThemeConfig extends Config
{
    public function group(): string
    {
        return 'theme';
    }

    public function form(): array
    {
        return [
            'logo_url' => '',
            'favicon_url' => '',
            'favicon_chrome_small_url' => '',
            'favicon_chrome_medium_url' => '',
            'app_color' => 'indigo',
            'app_custom_colors' => [
                'primary' => Arr::get(Theme::getDefault('primary'), 'indigo'),
                'primary_context' => Theme::getDefault('primary_context'),
                'alert' => Theme::getDefault('alert'),
                'alert_context' => Theme::getDefault('alert_context'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'logo_url' => ['sometimes', 'nullable', 'string', 'max:255'],
            'favicon_url' => ['sometimes', 'nullable', 'string', 'max:255'],
            'favicon_chrome_small_url' => ['sometimes', 'nullable', 'string', 'max:255'],
            'favicon_chrome_medium_url' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];

        // TODO: Implement THEME rules() method.
    }

    public function messages(): array
    {
        return [
            'logo_url.required' => __('mixpost-enterprise::theme.logo_required'),
            'primary_color.required' => __('mixpost-enterprise::theme.primary_color_required'),
        ];
    }
}
