<?php

namespace Inovector\Mixpost\Http\Api\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Gate;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends Middleware
{
    use UsesUserModel;

    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);
        } catch (AuthenticationException $e) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        if (! Gate::allows('viewMixpost')) {
            return response()->json(['message' => 'Access forbidden.'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
