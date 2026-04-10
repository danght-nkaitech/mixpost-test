<?php

namespace Inovector\MixpostEnterprise\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\MixpostEnterprise\Configs\SystemConfig;
use Inovector\MixpostEnterprise\Models\Workspace;
use Symfony\Component\HttpFoundation\Response;

class AllowMultipleWorkspaces
{
    use UsesAuth;

    public function handle(Request $request, Closure $next): Response
    {
        $multipleWorkspacesEnabled = app(SystemConfig::class)->multipleWorkspacesEnabled();

        if ($multipleWorkspacesEnabled || ! Workspace::ownedBy(self::getAuthGuard()->user())->exists()) {
            return $next($request);
        }

        abort(403);
    }
}
