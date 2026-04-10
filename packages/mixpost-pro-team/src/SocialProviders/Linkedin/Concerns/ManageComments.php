<?php

namespace Inovector\Mixpost\SocialProviders\Linkedin\Concerns;

use Inovector\Mixpost\Support\SocialProviderResponse;

trait ManageComments
{
    public function publishComment(string $text, string $postId, array $params = []): SocialProviderResponse
    {
        $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->withHeaders($this->httpHeadersNext())
            ->post("$this->apiUrl/rest/socialActions/$postId/comments", [
                'actor' => "urn:li:{$this->author()}:{$this->values['provider_id']}",
                'object' => $postId,
                'message' => [
                    'text' => $text,
                ],
            ]);

        return $this->buildResponse($response);
    }
}
