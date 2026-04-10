<?php

namespace Inovector\Mixpost\SocialProviders\Threads\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait ManagesContainer
{
    public function createContainer(?Media $mediaItem = null, array $data = []): SocialProviderResponse
    {
        if (! Arr::has($data, 'media_type')) {
            $data['media_type'] = $mediaItem ? ($mediaItem->isVideo() ? 'VIDEO' : 'IMAGE') : 'TEXT';
        }

        if ($mediaItem) {
            $data[$mediaItem->isVideo() ? 'video_url' : 'image_url'] = $mediaItem->getUrl();
        }

        if ($mediaItem && $mediaItem->alt_text) {
            $data['alt_text'] = $mediaItem->alt_text;
        }

        return $this->buildResponse(
            $this->getHttpClient()::withToken($this->accessToken())
                ->post("$this->graphUrl/$this->graphVersion/{$this->values['provider_id']}/threads", $data)
        );
    }

    public function getContainer(string $containerId): SocialProviderResponse
    {
        return $this->buildResponse(
            Http::withToken($this->accessToken())
                ->get("$this->graphUrl/$this->graphVersion/$containerId", [
                    'fields' => 'status,error_message',
                ])
        );
    }

    public function publishContainer(string $creationId, array $data = []): SocialProviderResponse
    {
        return $this->buildResponse(
            $this->getHttpClient()::withToken($this->accessToken())
                ->post("$this->graphUrl/$this->graphVersion/{$this->values['provider_id']}/threads_publish",
                    array_merge(['creation_id' => $creationId], $data)
                )
        );
    }
}
