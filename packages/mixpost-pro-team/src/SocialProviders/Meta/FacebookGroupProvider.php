<?php

namespace Inovector\Mixpost\SocialProviders\Meta;

use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\SocialProviders\Meta\Concerns\ManagesFacebookGroupResources;
use Inovector\Mixpost\SocialProviders\Meta\Concerns\ManagesFacebookOAuth;

// @deprecated
// We will remove this feature soon
class FacebookGroupProvider extends MetaProvider
{
    use ManagesFacebookGroupResources;
    use ManagesFacebookOAuth;

    public bool $onlyUserAccount = false;

    public static function name(): string
    {
        return 'Facebook';
    }

    protected function accessToken(): string
    {
        return $this->getAccessToken()['access_token'];
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        return "https://www.facebook.com/{$accountResource->pivot->provider_post_id}";
    }

    public static function externalAccountUrl(AccountResource $accountResource): string
    {
        return "https://www.facebook.com/groups/$accountResource->provider_id";
    }
}
