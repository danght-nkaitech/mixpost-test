<?php

namespace Inovector\Mixpost\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Facades\WorkspaceManager;

class CheckWorkspaceUser
{
    public function handle(Request $request, Closure $next, ?string $role = null)
    {
        $roles = ! $role ? [] : array_map(fn ($roleItem) => WorkspaceUserRole::fromName($roleItem), explode('|', $role));

        if (Auth::user()
            ->hasWorkspace(
                WorkspaceManager::current(),
                empty($roles) ? null : $roles
            )
        ) {
            return $next($request);
        }

        if (! $request->expectsJson()) {
            abort(403);
        }

        return response()->json([
            'message' => 'Access forbidden.',
        ], 403);
    }
}
