<?php

namespace Inovector\Mixpost\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Inovector\Mixpost\Contracts\UrlShortenerProvider connect()
 * @method static \Inovector\Mixpost\Contracts\UrlShortenerProvider connectProvider(string $name)
 * @method static array providers()
 * @method static bool isAnyServiceActive()
 * @method static bool isReadyToUse()
 * @method static string|null getDefaultProviderName()
 * @method static array getProviderSelectionOptions()
 * @method static array getProviderSelectionOptionKeys()
 *
 * @see \Inovector\Mixpost\Abstracts\UrlShortenerManager
 */
class UrlShortenerManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'MixpostUrlShortenerManager';
    }
}
