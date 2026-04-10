<?php

namespace Inovector\MixpostEnterprise\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Mixpost;
use Inovector\MixpostEnterprise\Facades\Impersonation as ImpersonationFacade;
use Symfony\Component\HttpFoundation\Response;

class Impersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        Mixpost::setImpersonating(ImpersonationFacade::impersonating());

        return $next($request);
    }
}
