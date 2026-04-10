<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

use Illuminate\Http\Client\Response;
use Inovector\Mixpost\Concerns\OAuth\UsesOAuthCodeChallenge;
use Inovector\Mixpost\Concerns\OAuth\UsesOAuthDPoPSession;
use Inovector\Mixpost\Exceptions\OAuthInvalidGrant;
use Inovector\Mixpost\Services\Bluesky\Crypt\DPoP;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait UsesTokenRequest
{
    use UsesAccessToken;
    use UsesOAuthCodeChallenge;
    use UsesOAuthDPoPSession;

    protected string $DPoPKeySource = 'session';

    protected function sendTokenRequest(string $token_url, array $payload): array
    {
        return $this->getHttpClient()::retry(times: 2, throw: false)
            ->withRequestMiddleware($this->tokenRequestMiddleware(...))
            ->withResponseMiddleware($this->tokenResponseMiddleware(...))
            ->post($token_url, $payload)
            ->throwIf(fn (Response $response) => $response->serverError())
            ->json();
    }

    protected function tokenRequestMiddleware(RequestInterface $request): RequestInterface
    {
        $dpopNonce = $this->getOAuthSession()->get(DPoP::AUTH_NONCE, '');

        $dpopProof = DPoP::authProof(
            jwk: DPoP::load($this->_getDPoPKey()),
            url: (string) $request->getUri(),
            nonce: $dpopNonce,
        );

        return $request->withHeader('DPoP', $dpopProof);
    }

    protected function tokenResponseMiddleware(ResponseInterface $response): ResponseInterface
    {
        $res = new Response($response);

        if ($res->status() === 400 && $res->json('error') === 'invalid_grant') {
            throw new OAuthInvalidGrant;
        }

        $dpopNonce = $res->header('DPoP-Nonce');

        $this->getOAuthSession()->put(DPoP::AUTH_NONCE, $dpopNonce);

        $sub = $res->json('sub');

        if (filled($sub)) {
            $this->getOAuthSession()->put('sub', $sub);
        }

        return $response;
    }

    public function getAccessTokenResponse(string $code): array
    {
        return $this->sendTokenRequest($this->getTokenUrl(), $this->getTokenFields($code));
    }

    protected function getTokenUrl(): string
    {
        return (string) $this->authServerMeta('token_endpoint');
    }

    protected function getTokenFields(string $code): array
    {
        return [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_assertion_type' => self::CLIENT_ASSERTION_TYPE,
            'client_assertion' => $this->getClientAssertion(),
            'code_verifier' => $this->getCodeVerifierSession(request()),
        ];
    }

    protected function setDPoPKeySourceSession(): void
    {
        $this->DPoPKeySource = 'session';
    }

    protected function setDPoPKeySourceDatabase(): void
    {
        $this->DPoPKeySource = 'database';
    }

    protected function resetDPoPKeySource(): void
    {
        $this->setDPoPKeySourceSession();
    }

    private function isDPoPKeySourceSession(): bool
    {
        return $this->DPoPKeySource === 'session';
    }

    private function _getDPoPKey(): ?string
    {
        if ($this->isDPoPKeySourceSession()) {
            return $this->getDPoPKeySession($this->request);
        }

        return $this->getDPoPKey();
    }
}
