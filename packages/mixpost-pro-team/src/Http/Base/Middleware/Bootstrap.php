<?php

namespace Inovector\Mixpost\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as AuthFacade;
use Inovector\Mixpost\Concerns\UsesAuth;

class Bootstrap
{
    use UsesAuth;

    public function handle(Request $request, Closure $next)
    {
        AuthFacade::shouldUse(self::getAuthGuardName());

        date_default_timezone_set('UTC');

        return $next($request);
    }
}
