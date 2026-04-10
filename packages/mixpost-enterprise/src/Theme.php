<?php

namespace Inovector\MixpostEnterprise;

use Illuminate\Support\Arr;
use Inovector\MixpostEnterprise\Configs\ThemeConfig;

class Theme
{
    public static function defaultColors(): array
    {
        return [
            'primary' => [
                'indigo' => [
                    '50' => '#EDECF8',
                    '100' => '#DCDAF1',
                    '200' => '#B8B4E4',
                    '500' => '#4F46BB',
                    '700' => '#2F2970',
                    '800' => '#1F1B4B',
                    '900' => '#100E25',
                ],
                'cyan' => [
                    '50' => '#ecfeff',
                    '100' => '#e0f2fe',
                    '200' => '#bae6fd',
                    '500' => '#0ea5e9',
                    '700' => '#0369a1',
                    '800' => '#075985',
                    '900' => '#0c4a6e',
                ],
                'orange' => [
                    '50' => '#fff7ed',
                    '100' => '#ffedd5',
                    '200' => '#fed7aa',
                    '500' => '#f97316',
                    '700' => '#c2410c',
                    '800' => '#9a3412',
                    '900' => '#7c2d12',
                ],
                'red' => [
                    '50' => '#fef2f2',
                    '100' => '#fee2e2',
                    '200' => '#fecaca',
                    '500' => '#ef4444',
                    '700' => '#b91c1c',
                    '800' => '#991b1b',
                    '900' => '#7f1d1d',
                ],
                'yellow' => [
                    '50' => '#fefce8',
                    '100' => '#fef9c3',
                    '200' => '#fef08a',
                    '500' => '#eab308',
                    '700' => '#a16207',
                    '800' => '#854d0e',
                    '900' => '#713f12',
                ],
                'green' => [
                    '50' => '#f0fdf4',
                    '100' => '#dcfce7',
                    '200' => '#bbf7d0',
                    '500' => '#22c55e',
                    '700' => '#15803d',
                    '800' => '#166534',
                    '900' => '#14532d',
                ],
                'blue' => [
                    '50' => '#eff6ff',
                    '100' => '#dbeafe',
                    '200' => '#bfdbfe',
                    '500' => '#3b82f6',
                    '700' => '#1d4ed8',
                    '800' => '#1e40af',
                    '900' => '#1e3a8a',
                ],
                'pink' => [
                    '50' => '#fdf2f8',
                    '100' => '#fce7f3',
                    '200' => '#fbcfe8',
                    '500' => '#ec4899',
                    '700' => '#be123c',
                    '800' => '#9f1239',
                    '900' => '#881337',
                ],
            ],
            'primary_context' => '#ffffff',
            'alert' => '#1F1B4B',
            'alert_context' => '#e5e7eb',
        ];
    }

    public function __construct(public readonly ThemeConfig $config) {}

    public function config(): ThemeConfig
    {
        return $this->config;
    }

    public function getDefault(string $key, ?string $default = null): mixed
    {
        return Arr::get(self::defaultColors(), $key, $default);
    }

    public function configuredColors(): array
    {
        $appColor = $this->config->get('app_color');

        $primaryColors = Arr::get(
            $this->getDefault('primary'),
            $appColor,
            $this->getDefault('primary')['indigo']
        );

        $customColors = $this->config->get('app_custom_colors');

        if ($appColor === 'custom') {
            $primaryColors = Arr::map($primaryColors, function ($default, $key) use ($customColors) {
                return Arr::get($customColors['primary'], $key, $default);
            });
        }

        return [
            'primary_colors' => $primaryColors,
            'primary_ring_focus' => self::hex2rgba(Arr::get($primaryColors, '200', '#000'), 0.5),
            'primary_context' => Arr::get($customColors, 'primary_context', $this->getDefault('primary_context')),
            'alert' => Arr::get($customColors, 'alert', $this->getDefault('alert')),
            'alert_context' => Arr::get($customColors, 'alert_context', $this->getDefault('alert_context')),
        ];
    }

    private static function hex2rgba($color, $opacity = false): string
    {
        $defaultColor = 'rgb(0,0,0)';

        if (empty($color)) {
            return $defaultColor;
        }

        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) == 6) {
            $hex = [$color[0].$color[1], $color[2].$color[3], $color[4].$color[5]];
        } elseif (strlen($color) == 3) {
            $hex = [$color[0].$color[0], $color[1].$color[1], $color[2].$color[2]];
        } else {
            return $defaultColor;
        }

        $rgb = array_map('hexdec', $hex);

        if ($opacity) {
            if (abs($opacity) > 1) {
                $opacity = 1.0;
            }
            $output = 'rgba('.implode(',', $rgb).','.$opacity.')';
        } else {
            $output = 'rgb('.implode(',', $rgb).')';
        }

        return $output;
    }
}
