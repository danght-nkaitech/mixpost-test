<?php

namespace Inovector\Mixpost\SocialProviders\Meta;

use Closure;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\Contracts\SocialProviderPostOptions;
use Inovector\Mixpost\SocialProviders\Meta\Concerns\ManagesFacebookOAuth;
use Inovector\Mixpost\SocialProviders\Meta\Concerns\ManagesInstagramResources;
use Inovector\Mixpost\SocialProviders\Meta\Support\InstagramPostOptions;
use Inovector\Mixpost\Support\SocialProviderPostConfigs;
use Inovector\Mixpost\Util;

class InstagramProvider extends MetaProvider
{
    use ManagesFacebookOAuth;
    use ManagesInstagramResources;

    public bool $onlyUserAccount = false;

    protected function setScope(): void
    {
        // Remove `publish_to_groups` scope.
        $newScopes = array_values(
            Arr::where($this->getSupportedScopeList(), function ($scope) {
                return $scope !== 'publish_to_groups';
            })
        );

        $this->scope = implode(',', $newScopes);
    }

    public static function postConfigs(Closure $getAccountData): SocialProviderPostConfigs
    {
        return SocialProviderPostConfigs::make()
            ->simultaneousPosting(Util::config('social_provider_options.instagram.simultaneous_posting_on_multiple_accounts'))
            ->minTextChar(0)
            ->maxTextChar(Util::config('social_provider_options.instagram.post_character_limit'))
            ->minPhotos(1)
            ->minPhotos(0, 'reel')
            ->minGifs(0, 'reel')
            ->minVideos(1)
            ->maxPhotos(Util::config('social_provider_options.instagram.media_limit.photos'))
            ->maxPhotos(0, 'reel')
            ->maxPhotos(1, 'story')
            ->maxVideos(Util::config('social_provider_options.instagram.media_limit.videos'))
            ->maxVideos(1, 'reel')
            ->maxGifs(Util::config('social_provider_options.instagram.media_limit.gifs'))
            ->allowMixingMediaTypes(true, 'post')
            ->allowMixingMediaTypes(false, 'reel')
            ->enableVideoThumb(true, 'reel');
    }

    public static function postOptions(): SocialProviderPostOptions
    {
        return new InstagramPostOptions;
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        $data = $accountResource->pivot->data ? json_decode($accountResource->pivot->data, true) : [];

        if (Arr::get($data, 'story')) {
            return "https://www.instagram.com/stories/$accountResource->username/";
        }

        $shortcode = Arr::get($data, 'shortcode');

        if (! $shortcode) {
            return '';
        }

        return "https://www.instagram.com/p/$shortcode/";
    }

    public static function externalAccountUrl(AccountResource $accountResource): string
    {
        return "https://www.instagram.com/$accountResource->username";
    }
}
