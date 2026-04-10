<?php

namespace Inovector\Mixpost\Http\Api\Middleware;

use Closure;
use Illuminate\Http\Request;

class Bootstrap
{
    public function handle(Request $request, Closure $next)
    {
        date_default_timezone_set('UTC');

        return $next($request);
    }
}
