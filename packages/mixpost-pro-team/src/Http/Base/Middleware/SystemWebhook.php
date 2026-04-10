<?php

namespace Inovector\Mixpost\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Mixpost;

class SystemWebhook
{
    public function handle(Request $request, Closure $next)
    {
        if (! Mixpost::isSystemWebhookEnabled()) {
            abort(403);
        }

        return $next($request);
    }
}
