<?php

namespace Inovector\Mixpost\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Inovector\Mixpost\Contracts\AIProvider connect()
 * @method static \Inovector\Mixpost\Contracts\AIProvider connectProvider(string $name)
 * @method static array providers()
 * @method static bool isAnyServiceActive()
 * @method static bool isReadyToUse()
 * @method static string|null getDefaultProviderName()
 * @method static array getProviderSelectionOptions()
 * @method static array getProviderSelectionOptionKeys()
 *
 * @see \Inovector\Mixpost\Abstracts\AIManager
 */
class AIManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'MixpostAIManager';
    }
}
