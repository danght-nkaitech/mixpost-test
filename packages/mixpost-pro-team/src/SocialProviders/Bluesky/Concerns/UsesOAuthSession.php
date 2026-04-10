<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

use Inovector\Mixpost\SocialProviders\Bluesky\Support\OAuthSession;

trait UsesOAuthSession
{
    protected ?OAuthSession $session = null;

    public function getOAuthSession(): OAuthSession
    {
        if (is_null($this->session)) {
            $this->session = new OAuthSession;
        }

        return $this->session;
    }
}
