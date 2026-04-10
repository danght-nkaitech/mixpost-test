<?php

namespace Inovector\Mixpost\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Facades\WorkspaceManager;

class IdentifyWorkspaceForCallback
{
    public function handle(Request $request, Closure $next)
    {
        if (WorkspaceManager::loadByUuid((string) $request->get('state'))) {
            return $next($request);
        }

        abort(404);
    }
}
