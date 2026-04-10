<?php

namespace Inovector\Mixpost\SocialProviders\Threads\Concerns;

use Illuminate\Support\Collection;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Support\SocialProviderResponse;
use RuntimeException;

trait CarouselPost
{
    use ManagesContainer;
    use ManagesContainerPublishing;
    use UsesAccessToken;

    protected function createCarouselPost(Collection $media, array $data = []): SocialProviderResponse
    {
        try {
            $creationIds = $this->createCarouselItems($media);

            $carouselContainerResponse = $this->createCarouselContainer($creationIds, $data);

            $this->waitForContainerCompletion($carouselContainerResponse);

            return $this->publishContainerAndRetrieveMedia($carouselContainerResponse);
        } catch (RuntimeException $e) {
            return $this->response(SocialProviderResponseStatus::ERROR, json_decode($e->getMessage(), true));
        }
    }

    private function createCarouselItems(Collection $media): array
    {
        $creationIds = [];

        foreach ($media as $mediaItem) {
            $response = $this->createMediaItemContainer($mediaItem);

            $this->waitForContainerCompletion($response);

            $creationIds[] = $response->id();
        }

        return $creationIds;
    }

    private function createMediaItemContainer(Media $mediaItem): SocialProviderResponse
    {
        $response = $this->createContainer(
            mediaItem: $mediaItem,
            data: ['is_carousel_item' => true]
        );

        if ($response->hasError()) {
            throw new RuntimeException(json_encode($response->context()));
        }

        return $response;
    }

    private function createCarouselContainer(array $creationIds, array $data): SocialProviderResponse
    {
        $response = $this->createContainer(
            data: array_merge([
                'media_type' => 'CAROUSEL',
                'children' => implode(',', $creationIds),
            ], $data)
        );

        if ($response->hasError()) {
            throw new RuntimeException(json_encode($response->context()));
        }

        return $response;
    }
}
