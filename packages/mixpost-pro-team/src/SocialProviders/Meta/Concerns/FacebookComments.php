<?php

namespace Inovector\Mixpost\SocialProviders\Meta\Concerns;

use Illuminate\Support\Arr;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait FacebookComments
{
    public function publishComment(string $text, string $postId, array $params = []): SocialProviderResponse
    {
        if (Arr::get($params, 'type') === 'story') {
            return $this->response(SocialProviderResponseStatus::OK, []);
        }

        $response = $this->getHttpClient()::withToken($this->accessToken())
            ->post("$this->apiUrl/$this->apiVersion/$postId/comments", [
                'message' => $text,
            ]);

        return $this->buildResponse($response);
    }
}
