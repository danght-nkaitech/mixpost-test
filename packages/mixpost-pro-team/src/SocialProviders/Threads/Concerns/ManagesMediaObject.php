<?php

namespace Inovector\Mixpost\SocialProviders\Threads\Concerns;

use Illuminate\Support\Facades\Http;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait ManagesMediaObject
{
    use UsesAccessToken;

    public function getMedia(string $threadMediaId): SocialProviderResponse
    {
        return $this->buildResponse(
            Http::withToken($this->accessToken())
                ->get("$this->graphUrl/$this->graphVersion/$threadMediaId", [
                    'fields' => 'permalink',
                ])
        );
    }
}
