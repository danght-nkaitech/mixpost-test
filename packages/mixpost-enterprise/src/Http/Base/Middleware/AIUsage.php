<?php

namespace Inovector\MixpostEnterprise\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\FeatureLimitResources\AICredits;
use Symfony\Component\HttpFoundation\Response;

class AIUsage
{
    public function handle(Request $request, Closure $next): Response
    {
        app(AICredits::class)
            ->limits(WorkspaceManager::current()->limits)
            ->validator((object) [
                'workspace' => WorkspaceManager::current(),
            ])
            ->validate();

        return $next($request);
    }
}
