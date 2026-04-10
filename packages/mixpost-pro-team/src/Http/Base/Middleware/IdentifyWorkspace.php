<?php

namespace Inovector\Mixpost\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Facades\WorkspaceManager;

class IdentifyWorkspace
{
    public function handle(Request $request, Closure $next)
    {
        if (WorkspaceManager::loadByUuid($request->route('workspace'))) {
            return $next($request);
        }

        if (! $request->expectsJson()) {
            abort(404);
        }

        return response()->json([
            'message' => 'Workspace not found.',
        ], 404);
    }
}
