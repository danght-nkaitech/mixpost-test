<?php

namespace Inovector\Mixpost\Concerns\OAuth;

use Illuminate\Http\Request;
use Inovector\Mixpost\Concerns\UsesCacheKey;
use Inovector\Mixpost\Services\Bluesky\Crypt\DPoP;

trait UsesOAuthDPoPSession
{
    use UsesCacheKey;

    protected function newDPoPKeySession(Request $request): void
    {
        $request->session()->put($this->sessionKey(), DPoP::generate());
    }

    protected function getDPoPKeySession(Request $request): ?string
    {
        return $request->session()->get($this->sessionKey());
    }

    protected function clearDPoPKeySession(Request $request): void
    {
        $request->session()->forget([
            $this->sessionKey(),
        ]);
    }

    private function sessionKey(): string
    {
        return $this->resolveCacheKey('dpop_key');
    }
}
