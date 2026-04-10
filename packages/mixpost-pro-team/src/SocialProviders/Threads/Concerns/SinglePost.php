<?php

namespace Inovector\Mixpost\SocialProviders\Threads\Concerns;

use Illuminate\Support\Arr;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Support\SocialProviderResponse;
use RuntimeException;

trait SinglePost
{
    use ManagesContainer;
    use ManagesContainerPublishing;
    use UsesAccessToken;

    public function createSinglePost(?Media $mediaItem = null, array $data = []): SocialProviderResponse
    {
        try {
            $isTextPost = $this->isTextPost($mediaItem, $data);

            $containerResponse = $this->createPostContainer($mediaItem, $data, $isTextPost);

            if (! $isTextPost) {
                $this->waitForContainerCompletion($containerResponse);
            }

            return $this->publishContainerAndRetrieveMedia($containerResponse);
        } catch (RuntimeException $e) {
            return $this->response(SocialProviderResponseStatus::ERROR, json_decode($e->getMessage(), true));
        }
    }

    private function isTextPost(?Media $mediaItem, array $data): bool
    {
        return empty($mediaItem) && Arr::get($data, 'text', '') !== '';
    }

    private function createPostContainer(?Media $mediaItem, array $data, bool $isTextPost): SocialProviderResponse
    {
        $response = $this->createContainer(
            mediaItem: $mediaItem,
            data: ! $isTextPost
                ? array_merge(['is_carousel_item' => false], $data)
                : $data
        );

        if ($response->hasError()) {
            throw new RuntimeException(json_encode($response->context()));
        }

        return $response;
    }
}
