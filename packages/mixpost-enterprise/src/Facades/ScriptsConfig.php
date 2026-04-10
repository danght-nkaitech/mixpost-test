<?php

namespace Inovector\MixpostEnterprise\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array save()
 * @method static mixed get(string $name)
 * @method static array all()
 * @method static array form()
 * @method static array rules()
 * @method static array messages()
 *
 * @see \Inovector\MixpostEnterprise\Configs\ScriptsConfig
 */
class ScriptsConfig extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'MixpostEnterpriseScriptsConfig';
    }
}
