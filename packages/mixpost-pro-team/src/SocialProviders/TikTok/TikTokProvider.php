<?php

namespace Inovector\Mixpost\SocialProviders\TikTok;

use Closure;
use Inovector\Mixpost\Abstracts\SocialProvider;
use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\Contracts\SocialProviderPostOptions as SocialProviderPostOptionsContract;
use Inovector\Mixpost\Services\TikTokService;
use Inovector\Mixpost\SocialProviders\TikTok\Concerns\ManagesConfig;
use Inovector\Mixpost\SocialProviders\TikTok\Concerns\ManagesOAuth;
use Inovector\Mixpost\SocialProviders\TikTok\Concerns\ManagesRateLimit;
use Inovector\Mixpost\SocialProviders\TikTok\Concerns\ManagesResources;
use Inovector\Mixpost\SocialProviders\TikTok\Support\TikTokPostOptions;
use Inovector\Mixpost\Support\SocialProviderPostConfigs;
use Inovector\Mixpost\Util;

class TikTokProvider extends SocialProvider
{
    public array $callbackResponseKeys = ['code'];

    use ManagesConfig;
    use ManagesOAuth;
    use ManagesRateLimit;
    use ManagesResources;

    public function identifier(): string
    {
        return 'tiktok';
    }

    public static function name(): string
    {
        return 'TikTok';
    }

    public static function service(): string
    {
        return TikTokService::class;
    }

    public static function postConfigs(Closure $getAccountData): SocialProviderPostConfigs
    {
        return SocialProviderPostConfigs::make()
            ->simultaneousPosting(Util::config('social_provider_options.tiktok.simultaneous_posting_on_multiple_accounts'))
            ->maxTextChar(Util::config('social_provider_options.tiktok.post_character_limit'))
            ->minVideos(1)
            ->maxPhotos(Util::config('social_provider_options.tiktok.media_limit.photos'))
            ->maxVideos(Util::config('social_provider_options.tiktok.media_limit.videos'))
            ->maxGifs(Util::config('social_provider_options.tiktok.media_limit.gifs'))
            ->allowMixingMediaTypes(Util::config('social_provider_options.tiktok.allow_mixing'))
            ->mediaTextRequirementOperator('and');
    }

    public static function postOptions(): SocialProviderPostOptionsContract
    {
        return new TikTokPostOptions;
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        if (! $accountResource->pivot->provider_post_id) {
            return '';
        }

        return "https://www.tiktok.com/@$accountResource->username/video/{$accountResource->pivot->provider_post_id}";
    }

    public static function mapErrorMessage(string $key): string
    {
        return match ($key) {
            'access_token_expired' => __('mixpost::account.access_token_expired'),
            'video_not_selected' => __('mixpost::post.video_not_selected'),
            'supports_only_videos' => __('mixpost::service.tiktok.supports_only_videos'),
            'content_disclosure_required' => __('mixpost::service.tiktok.content_disclosure_required'),
            default => $key
        };
    }

    public static function supportPostDeletion(): bool|array
    {
        return false;
    }
}
