<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

use Illuminate\Http\Client\Response;
use Inovector\Mixpost\Concerns\OAuth\UsesOAuthCodeChallenge;
use Inovector\Mixpost\Concerns\OAuth\UsesOAuthDPoPSession;
use Inovector\Mixpost\Services\Bluesky\Crypt\DPoP;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait UsesPAR
{
    use UsesOAuthCodeChallenge;
    use UsesOAuthDPoPSession;
    use UsesOAuthSession;
    use UsesScopes;

    protected function getParRequestUrl(): string
    {
        $parData = $this->parRequestFields();

        return $this->sendParRequest($parData)
            ->json('request_uri', '');
    }

    protected function sendParRequest(array $par_data): Response
    {
        $parUrl = $this->authServerMeta('pushed_authorization_request_endpoint');

        throw_if(empty($parUrl));

        return $this->getHttpClient()::asForm()
            ->withRequestMiddleware($this->parRequestMiddleware(...))
            ->withResponseMiddleware($this->parResponseMiddleware(...))
            ->retry(times: 2, throw: false)
            ->throw()
            ->post($parUrl, $par_data);
    }

    protected function parRequestFields(): array
    {
        return [
            'response_type' => 'code',
            'code_challenge' => $this->getCodeChallenge($this->request),
            'code_challenge_method' => $this->getCodeChallengeMethod(),
            'client_id' => $this->clientId,
            'state' => $this->values['state'],
            'redirect_uri' => $this->redirectUrl,
            'scope' => $this->formatScopes(),
            'client_assertion_type' => self::CLIENT_ASSERTION_TYPE,
            'client_assertion' => $this->getClientAssertion(),
            'login_hint' => null,
        ];
    }

    protected function parRequestMiddleware(RequestInterface $request): RequestInterface
    {
        $dpopNonce = $this->getOAuthSession()->get(DPoP::AUTH_NONCE, '');

        $dpopProof = DPoP::authProof(
            jwk: DPoP::load($this->getDPoPKeySession($this->request)),
            url: (string) $request->getUri(),
            nonce: $dpopNonce,
        );

        return $request->withHeader('DPoP', $dpopProof);
    }

    protected function parResponseMiddleware(ResponseInterface $response): ResponseInterface
    {
        $dpopNonce = (string) collect($response->getHeader('DPoP-Nonce'))->first();

        $this->getOAuthSession()->put(DPoP::AUTH_NONCE, $dpopNonce);

        return $response;
    }
}
