<?php

namespace Inovector\Mixpost\SocialProviders\Threads\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait ManagesResources
{
    use CarouselPost;
    use SinglePost;

    public function getAccount(): SocialProviderResponse
    {
        $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->get("$this->graphUrl/$this->graphVersion/me", [
                'fields' => 'id,username,name,threads_profile_picture_url',
            ]);

        return $this->buildResponse($response, function () use ($response) {
            $data = $response->json();

            return [
                'id' => $data['id'],
                'name' => $data['name'] ?? $data['username'],
                'username' => $data['username'],
                'image' => $data['threads_profile_picture_url'] ?? '',
            ];
        });
    }

    public function publishPost(string $text, Collection $media, array $params = []): SocialProviderResponse
    {
        $data = [
            'text' => $text,
        ];

        if ($lastId = Arr::get($params, 'last_id')) {
            $data['reply_to_id'] = $lastId;
        }

        if ($media->count() === 0 || $media->count() === 1) {
            return $this->createSinglePost($media->first(), $data);
        }

        return $this->createCarouselPost($media, $data);
    }

    public function getAccountMetrics(): SocialProviderResponse
    {
        $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->get("$this->graphUrl/$this->graphVersion/{$this->values['provider_id']}/threads_insights", [
                'metric' => 'followers_count',
            ]);

        return $this->buildResponse($response, function () use ($response) {
            $data = $response->json();

            return [
                'followers_count' => Arr::get($data, 'data.0.total_value.value', 0),
            ];
        });
    }

    public function deletePost(string $id, array $params = []): SocialProviderResponse
    {
        $response = $this->getHttpClient()::delete("$this->graphUrl/$this->graphVersion/$id", [
            'access_token' => $this->accessToken(),
        ]);

        if ($response->json('error.code') === 100) {
            /**
             * Handle 100 error codes when attempting to delete a post that no longer exists on the platform.
             * This occurs when we have a stored post_provider_id but the post has already been deleted directly on the platform.
             */
            return $this->response(SocialProviderResponseStatus::OK, []);
        }

        return $this->buildResponse($response);
    }
}
