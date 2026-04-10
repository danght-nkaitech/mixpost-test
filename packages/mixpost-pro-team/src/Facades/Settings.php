<?php

namespace Inovector\Mixpost\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array form()
 * @method static array rules()
 * @method mixed get(string $name, ?int $userId = null)
 * @method static getFromCache(string $name, mixed $default = null, ?int $userId = null)
 * @method static array all(?int $userId = null)
 * @method static void forget(string $name, ?int $userId = null)
 * @method static void forgetAll(?int $userId = null)
 * @method static void put(string $name, mixed $value = null, ?int $userId = null)
 *
 * @see \Inovector\Mixpost\Settings
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'MixpostSettings';
    }
}
