<?php

namespace Inovector\MixpostEnterprise\Facades;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void impersonate(Authenticatable $user)
 * @method static void stopImpersonating()
 * @method static bool canImpersonate()
 * @method static bool impersonating()
 *
 * @see \Inovector\MixpostEnterprise\Impersonation
 */
class Impersonation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'MixpostEnterpriseImpersonation';
    }
}
