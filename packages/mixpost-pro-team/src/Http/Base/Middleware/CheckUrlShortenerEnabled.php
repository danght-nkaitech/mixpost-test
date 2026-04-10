<?php

namespace Inovector\Mixpost\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Configs\GeneralConfig;
use Symfony\Component\HttpFoundation\Response;

class CheckUrlShortenerEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if (app(GeneralConfig::class)->get('url_shortener_provider') === 'disabled') {
            return response()->json([
                'message' => 'URL Shortener disabled.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
