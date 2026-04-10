<?php

namespace Inovector\Mixpost\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inovector\Mixpost\Facades\AIManager;

class CheckAIConfiguration
{
    /**
     * @throws ValidationException
     */
    public function handle(Request $request, Closure $next)
    {
        if (! AIManager::isReadyToUse()) {
            throw ValidationException::withMessages([
                'ai_disabled' => [__('mixpost::ai.ai_disabled')],
            ]);
        }

        return $next($request);
    }
}
