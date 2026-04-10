<?php

namespace Inovector\Mixpost\SocialProviders\Meta;

use Illuminate\Support\Arr;
use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\Contracts\SocialProviderPostOptions as SocialProviderPostOptionsContract;
use Inovector\Mixpost\SocialProviders\Meta\Concerns\ManagesFacebookOAuth;
use Inovector\Mixpost\SocialProviders\Meta\Concerns\ManagesFacebookPageResources;
use Inovector\Mixpost\SocialProviders\Meta\Support\FacebookPagePostOptions;

class FacebookPageProvider extends MetaProvider
{
    use ManagesFacebookOAuth;
    use ManagesFacebookPageResources;

    public bool $onlyUserAccount = false;

    public static function name(): string
    {
        return 'Facebook';
    }

    protected function accessToken(): string
    {
        return $this->getAccessToken()['page_access_token'];
    }

    public static function postOptions(): SocialProviderPostOptionsContract
    {
        return new FacebookPagePostOptions;
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        $data = $accountResource->pivot->data ? json_decode($accountResource->pivot->data, true) : [];

        $domain = 'https://facebook.com';

        if (Arr::get($data, 'story') && $path = Arr::get($data, 'path')) {
            return "$domain/stories/$path?view_single=1";
        }

        if (Arr::get($data, 'story') && ! Arr::get($data, 'path')) {
            return "$domain/$accountResource->provider_id";
        }

        return "$domain/{$accountResource->pivot->provider_post_id}";
    }

    public static function externalAccountUrl(AccountResource $accountResource): string
    {
        $identifier = $accountResource->username ?: $accountResource->provider_id;

        return "https://www.facebook.com/$identifier";
    }

    public static function supportPostDeletion(): bool|array
    {
        return [
            'post' => true,
            'reel' => true,
            'story' => false,
        ];
    }
}
