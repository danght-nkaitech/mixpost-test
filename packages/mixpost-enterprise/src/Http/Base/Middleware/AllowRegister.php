<?php

namespace Inovector\MixpostEnterprise\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inovector\MixpostEnterprise\Configs\OnboardingConfig;
use Symfony\Component\HttpFoundation\Response;

class AllowRegister
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(OnboardingConfig::class)->allowRegister()) {
            abort(404);
        }

        return $next($request);
    }
}
