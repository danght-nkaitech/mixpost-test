<?php

namespace Inovector\Mixpost\Http\Base\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    use UsesAuth;
    use UsesUserModel;

    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return $this->redirectTo($request);
        }

        if (! Gate::allows('viewMixpost')) {
            abort(403);
        }

        // TODO: Find a better way to use the custom model instance
        if (! Auth::user() instanceof User) {
            $user = self::getUserClass()::make(Auth::user()
                ->only('name', 'email'))
                ->setAttribute('email_verified_at', Auth::user()->email_verified_at)
                ->setAttribute('id', Auth::id());

            Auth::setUser($user);
        }

        return $next($request);
    }

    protected function redirectTo(Request $request): JsonResponse|Response
    {
        if (! $request->expectsJson()) {
            $request->session()->put('url.intended', url()->current());

            return Inertia::location(route(config('mixpost.redirect_unauthorized_users_to_route')));
        }

        return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
    }
}
