<?php

namespace Inovector\MixpostEnterprise\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\MixpostEnterprise\Configs\OnboardingConfig;

class EnsureEmailIsVerified
{
    use UsesAuth;

    public function handle(Request $request, Closure $next)
    {
        if (
            $request->routeIs('mixpost.logout') ||
            $request->routeIs('mixpost_e.impersonate.stop') ||
            ($request->routeIs('mixpost.profile.*') && ! $request->routeIs('mixpost.profile.accessTokens.*'))
        ) {
            return $next($request);
        }

        if (! app(OnboardingConfig::class)->get('email_verification')) {
            return $next($request);
        }

        if (! self::getAuthGuard()->user() || ! self::getAuthGuard()->user()->hasVerifiedEmail()) {
            if ($request->inertia()) {
                return Inertia::location(route('mixpost_e.emailVerification.notice'));
            }

            return Redirect::away(route('mixpost_e.emailVerification.notice'));
        }

        return $next($request);
    }
}
