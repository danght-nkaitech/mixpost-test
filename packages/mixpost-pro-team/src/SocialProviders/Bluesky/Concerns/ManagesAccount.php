<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

use Inovector\Mixpost\Support\SocialProviderResponse;

trait ManagesAccount
{
    use UsesResponseBuilder;
    use UsesValues;

    public function getAccount(): SocialProviderResponse
    {
        $response = $this->http()->get('app.bsky.actor.getProfile', [
            'actor' => $this->getDid(),
        ]);

        return $this->buildResponse($response, function ($data) {
            return [
                'id' => $data['did'],
                'name' => $data['displayName'] ?: $data['handle'],
                'username' => $data['handle'],
                'image' => $data['avatar'] ?? '',
                'data' => [
                    'server' => $this->getServer(),
                ],
            ];
        });
    }
}
