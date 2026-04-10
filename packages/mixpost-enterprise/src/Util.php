<?php

namespace Inovector\MixpostEnterprise;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Util as CoreUtil;

class Util extends CoreUtil
{
    public static function corePath($withEnterpriseSuffix = false): string
    {
        return config('mixpost.core_path', 'mixpost').($withEnterpriseSuffix ? '/enterprise' : '');
    }

    public static function appName(): string
    {
        return Config::get('app.name');
    }

    public static function dateFormat(Carbon $datetime): string
    {
        return $datetime->tz(Settings::get('timezone'))->translatedFormat('M j, Y');
    }

    public static function currencies(): Collection
    {
        $jsonData = file_get_contents(__DIR__.'../../data/currencies.json');

        return collect(json_decode($jsonData, true))->values();
    }

    public static function currenciesSorted(): Collection
    {
        $currencies = self::currencies();

        $popularCurrencies = $currencies->whereIn('code', ['USD', 'EUR', 'GBP']);
        $remainingCurrencies = $currencies->whereNotIn('code', ['USD', 'EUR', 'GBP']);

        return $popularCurrencies->merge($remainingCurrencies);
    }

    public static function currenciesForSelect(): Collection
    {
        return self::currenciesSorted()->map(function ($item) {
            return [
                'key' => $item['code'],
                'label' => "{$item['code']} - {$item['name']}",
            ];
        });
    }

    public static function currency(string $currency)
    {
        return self::currencies()->where('code', $currency)->first();
    }

    public static function isWorkspaceRoutes(Request $request): bool
    {
        if (! $request->route()) {
            return false;
        }

        return Str::startsWith($request->route()->getName(), 'mixpost_e.workspace.');
    }
}
