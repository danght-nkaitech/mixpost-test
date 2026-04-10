<?php

namespace Inovector\Mixpost\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Facades\ServiceManager;
use Symfony\Component\HttpFoundation\Response;

class BlueskyService
{
    public function handle(Request $request, Closure $next)
    {
        if (! ServiceManager::isConfigured('bluesky') || ! ServiceManager::isActive('bluesky')) {
            abort(Response::HTTP_FORBIDDEN, 'Bluesky service is not configured or not active');
        }

        return $next($request);
    }
}
