<?php

namespace Inovector\Mixpost\SocialProviders\Google;

use Closure;
use Inovector\Mixpost\Abstracts\SocialProvider;
use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\Contracts\SocialProviderPostOptions;
use Inovector\Mixpost\Services\GoogleService;
use Inovector\Mixpost\SocialProviders\Google\Concerns\ManagesOAuth;
use Inovector\Mixpost\SocialProviders\Google\Concerns\ManagesYoutubeResources;
use Inovector\Mixpost\SocialProviders\Google\Concerns\UsesResponseBuilder;
use Inovector\Mixpost\SocialProviders\Google\Support\YoutubePostOptions;
use Inovector\Mixpost\Support\SocialProviderPostConfigs;
use Inovector\Mixpost\Util;

class YoutubeProvider extends SocialProvider
{
    public bool $onlyUserAccount = false;

    public array $callbackResponseKeys = ['code'];

    use ManagesOAuth;
    use ManagesYoutubeResources;
    use UsesResponseBuilder;

    public static function name(): string
    {
        return 'YouTube';
    }

    public static function service(): string
    {
        return GoogleService::class;
    }

    protected function getScopes(): array
    {
        return [
            'https://www.googleapis.com/auth/youtube',
            'https://www.googleapis.com/auth/youtube.upload',
        ];
    }

    public static function postConfigs(Closure $getAccountData): SocialProviderPostConfigs
    {
        return SocialProviderPostConfigs::make()
            ->simultaneousPosting(Util::config('social_provider_options.youtube.simultaneous_posting_on_multiple_accounts'))
            ->minTextChar(0)
            ->maxTextChar(Util::config('social_provider_options.youtube.post_character_limit'))
            ->minVideos(1)
            ->maxPhotos(Util::config('social_provider_options.youtube.media_limit.photos'))
            ->maxVideos(Util::config('social_provider_options.youtube.media_limit.videos'))
            ->maxGifs(Util::config('social_provider_options.youtube.media_limit.gifs'))
            ->allowMixingMediaTypes(Util::config('social_provider_options.youtube.allow_mixing'))
            ->enableVideoThumb(true);
    }

    public static function postOptions(): SocialProviderPostOptions
    {
        return new YoutubePostOptions;
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        return "https://www.youtube.com/watch?v={$accountResource->pivot->provider_post_id}";
    }

    public static function externalAccountUrl(AccountResource $accountResource): string
    {
        return "https://www.youtube.com/@$accountResource->username";
    }

    public static function mapErrorMessage(string $key): string
    {
        return match ($key) {
            'access_token_expired' => __('mixpost::account.access_token_expired'),
            'upload_failed' => __('mixpost::service.twitter.upload_failed'),
            'request_timeout' => __('mixpost::error.request_timeout'),
            'unknown_error' => __('mixpost::error.unknown_error'),
            'video_not_selected' => __('mixpost::post.video_not_selected'),
            default => $key
        };
    }

    public static function supportAnalytics(): bool
    {
        return false;
    }

    public static function supportPostDeletion(): bool|array
    {
        return true;
    }
}
