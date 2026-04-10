<?php

namespace Inovector\Mixpost\SocialProviders\Threads\Concerns;

use Inovector\Mixpost\Support\SocialProviderResponse;
use Inovector\Mixpost\Util;
use RuntimeException;

trait ManagesContainerPublishing
{
    use ManagesContainer;
    use ManagesMediaObject;

    protected function publishContainerAndRetrieveMedia(SocialProviderResponse $containerResponse): SocialProviderResponse
    {
        $publishResponse = $this->publishContainer($containerResponse->id());

        if ($publishResponse->hasError()) {
            throw new RuntimeException(json_encode($publishResponse->context()));
        }

        $mediaObject = $this->getMedia($publishResponse->id());

        if ($mediaObject->hasError()) {
            throw new RuntimeException(json_encode($mediaObject->context()));
        }

        return $publishResponse->useContext([
            'id' => $publishResponse->id(),
            'data' => [
                'permalink' => $mediaObject->permalink,
            ],
        ]);
    }

    protected function waitForContainerCompletion(SocialProviderResponse $response): void
    {
        $result = Util::performTaskWithDelay(function () use ($response) {
            $container = $this->getContainer($response->id());

            if ($container->status == 'IN_PROGRESS') {
                // Return null to continue checking
                return null;
            }

            return $container;
        }, 30);

        if ($result->status != 'FINISHED') {
            throw new RuntimeException(json_encode($result->context()));
        }
    }
}
