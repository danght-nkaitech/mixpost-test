<?php

namespace Inovector\MixpostEnterprise\Facades;

use Illuminate\Support\Facades\Facade;
use Inovector\MixpostEnterprise\Configs\ThemeConfig;

/**
 * @method static ThemeConfig config()
 * @method static mixed getDefault(string $key, ?string $default = null)
 * @method static array configuredColors()
 *
 * @see \Inovector\MixpostEnterprise\Theme
 */
class Theme extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'MixpostEnterpriseTheme';
    }
}
