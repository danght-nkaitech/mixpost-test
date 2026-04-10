<?php

namespace Inovector\MixpostEnterprise\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Symfony\Component\HttpFoundation\Response;

class WorkspaceOwner
{
    use UsesAuth;

    public function handle(Request $request, Closure $next): Response
    {
        if (WorkspaceManager::current()->isOwner(self::getAuthGuard()->user())) {
            return $next($request);
        }

        abort(403);
    }
}
