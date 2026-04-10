<?php

namespace Inovector\MixpostEnterprise\Http\Api\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Access forbidden.'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
