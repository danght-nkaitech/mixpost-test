<?php

namespace Inovector\Mixpost\Facades;

use Illuminate\Support\Facades\Facade;
use Inovector\Mixpost\Configs\ThemeConfig;

/**
 * @method static string render()
 * @method static ThemeConfig config()
 * @method static void setCustomColors($value)
 * @method static array colors()
 * @method static string primaryColor(string $weight = '500')
 *
 * @see \Inovector\Mixpost\Theme
 */
class Theme extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'MixpostTheme';
    }
}
