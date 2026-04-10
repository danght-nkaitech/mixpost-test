<?php

namespace Inovector\Mixpost\SocialProviders\Pinterest;

use Closure;
use Inovector\Mixpost\Abstracts\SocialProvider;
use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\Contracts\SocialProviderPostOptions as SocialProviderPostOptionsContract;
use Inovector\Mixpost\Services\PinterestService;
use Inovector\Mixpost\SocialProviders\Pinterest\Concerns\ManagesConfig;
use Inovector\Mixpost\SocialProviders\Pinterest\Concerns\ManagesOAuth;
use Inovector\Mixpost\SocialProviders\Pinterest\Concerns\ManagesRateLimit;
use Inovector\Mixpost\SocialProviders\Pinterest\Concerns\ManagesResources;
use Inovector\Mixpost\SocialProviders\Pinterest\Support\PinterestPostOptions;
use Inovector\Mixpost\Support\SocialProviderPostConfigs;
use Inovector\Mixpost\Util;

class PinterestProvider extends SocialProvider
{
    public array $callbackResponseKeys = ['code'];

    public string $apiVersion = 'v5';

    public string $apiUrlProduction = 'https://api.pinterest.com';

    public string $apiUrlSandbox = 'https://api-sandbox.pinterest.com';

    use ManagesConfig;
    use ManagesOAuth;
    use ManagesRateLimit;
    use ManagesResources;

    public static function service(): string
    {
        return PinterestService::class;
    }

    protected function getApiUrl(): string
    {
        if ($this->values['environment'] === 'sandbox') {
            return $this->apiUrlSandbox;
        }

        return $this->apiUrlProduction;
    }

    public static function postConfigs(Closure $getAccountData): SocialProviderPostConfigs
    {
        return SocialProviderPostConfigs::make()
            ->simultaneousPosting(Util::config('social_provider_options.pinterest.simultaneous_posting_on_multiple_accounts'))
            ->maxTextChar(Util::config('social_provider_options.pinterest.post_character_limit'))
            ->minPhotos(1)
            ->maxPhotos(Util::config('social_provider_options.pinterest.media_limit.photos'))
            ->maxVideos(1)
            ->enableVideoThumb(true)
            ->maxGifs(Util::config('social_provider_options.pinterest.media_limit.gifs'))
            ->allowMixingMediaTypes(Util::config('social_provider_options.pinterest.allow_mixing'));
    }

    public static function postOptions(): SocialProviderPostOptionsContract
    {
        return new PinterestPostOptions;
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        return "https://www.pinterest.com/pin/{$accountResource->pivot->provider_post_id}/";
    }

    public static function externalAccountUrl(AccountResource $accountResource): string
    {
        return "https://www.pinterest.com/$accountResource->provider_id";
    }

    public static function mapErrorMessage(string $key): string
    {
        return match ($key) {
            'access_token_expired' => __('mixpost::account.access_token_expired'),
            'no_media_selected' => __('mixpost::post.no_media_selected'),
            'not_support_video' => __('mixpost::service.pinterest.not_support_video'),
            'video_upload_failed' => __('mixpost::service.pinterest.video_upload_failed'),
            default => $key
        };
    }

    public static function supportPostDeletion(): bool|array
    {
        return true;
    }
}
