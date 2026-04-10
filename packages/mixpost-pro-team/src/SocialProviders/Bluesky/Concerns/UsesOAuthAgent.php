<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

use Illuminate\Http\Client\PendingRequest;
use Inovector\Mixpost\Services\Bluesky\Crypt\DPoP;
use Inovector\Mixpost\SocialProviders\Bluesky\Support\DidDocument;
use Inovector\Mixpost\SocialProviders\Bluesky\Support\Identity;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait UsesOAuthAgent
{
    use UsesAccessToken;
    use UsesOAuthSession;
    use UsesValues;

    public function http(): PendingRequest
    {
        return $this->getHttpClient()::withToken($this->getToken(), 'DPoP')
            ->withRequestMiddleware($this->apiRequestMiddleware(...))
            ->withResponseMiddleware($this->apiResponseMiddleware(...))
            ->retry(times: 2, throw: false)
            ->baseUrl($this->getBaseUrl());
    }

    private function apiRequestMiddleware(RequestInterface $request): RequestInterface
    {
        $dpopProof = DPoP::apiProof(
            jwk: DPoP::load($this->getDPoPKey()),
            iss: self::DEFAULT_SERVER,
            url: (string) $request->getUri(),
            token: $this->getToken(),
            nonce: $this->getOAuthSession()->get(DPoP::API_NONCE, ''),
            method: $request->getMethod(),
        );

        return $request->withHeader('DPoP', $dpopProof);
    }

    private function apiResponseMiddleware(ResponseInterface $response): ResponseInterface
    {
        $dpopNonce = (string) collect($response->getHeader('DPoP-Nonce'))->first();

        $this->getOAuthSession()->put(DPoP::API_NONCE, $dpopNonce);

        return $response;
    }

    private function identity(): Identity
    {
        return app(Identity::class);
    }

    private function didDoc(): DidDocument
    {
        $did = $this->identity()->resolveDID($this->getDid())->json();

        return DidDocument::make($did);
    }

    private function getBaseUrl(): string
    {
        $endpoint = $this->didDoc()->pdsServiceEndpoint(self::DEFAULT_SERVER);

        return $endpoint.'/xrpc/';
    }
}
