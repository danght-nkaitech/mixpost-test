<?php

namespace Inovector\Mixpost\Concerns\OAuth;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inovector\Mixpost\Concerns\UsesCacheKey;

trait UsesOAuthCodeChallenge
{
    use UsesCacheKey;

    protected function getCodeVerifier(): string
    {
        return Str::random(96);
    }

    protected function setCodeVerifierSession(Request $request, string $codeVerifier): void
    {
        $request->session()->put($this->codeVerifierKey(), $codeVerifier);
    }

    protected function getCodeVerifierSession(Request $request): ?string
    {
        return $request->session()->get($this->codeVerifierKey());
    }

    protected function clearCodeVerifierSession(Request $request): void
    {
        $request->session()->forget([
            $this->codeVerifierKey(),
        ]);
    }

    protected function getCodeChallenge(Request $request): string
    {
        $hashed = hash('sha256', $this->getCodeVerifierSession($request), true);

        return rtrim(strtr(base64_encode($hashed), '+/', '-_'), '=');
    }

    protected function getCodeChallengeMethod(): string
    {
        return 'S256';
    }

    private function codeVerifierKey(): string
    {
        return $this->resolveCacheKey('code_verifier');
    }
}
