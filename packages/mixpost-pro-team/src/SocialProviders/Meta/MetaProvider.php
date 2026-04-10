<?php

namespace Inovector\Mixpost\SocialProviders\Meta;

use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Abstracts\SocialProvider;
use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\Services\FacebookService;
use Inovector\Mixpost\SocialProviders\Meta\Concerns\ManagesConfig;
use Inovector\Mixpost\SocialProviders\Meta\Concerns\ManagesMetaResources;
use Inovector\Mixpost\SocialProviders\Meta\Concerns\ManagesRateLimit;
use Inovector\Mixpost\SocialProviders\Meta\Concerns\MetaOauth;
use Inovector\Mixpost\Support\SocialProviderPostConfigs;
use Inovector\Mixpost\Util;

class MetaProvider extends SocialProvider
{
    use ManagesConfig;
    use ManagesMetaResources;
    use ManagesRateLimit;
    use MetaOauth;

    public array $callbackResponseKeys = ['code'];

    protected string $apiVersion;

    protected string $apiUrl = 'https://graph.facebook.com';

    protected string $scope;

    public function __construct(Request $request, string $clientId, string $clientSecret, string $redirectUrl, array $values = [])
    {
        $this->setApiVersion();

        $this->setScope();

        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $values);
    }

    public static function service(): string
    {
        return FacebookService::class;
    }

    protected function setApiVersion(): void
    {
        $this->apiVersion = $this->getApiVersionConfig();
    }

    protected function setScope(): void
    {
        $this->scope = implode(',', $this->getSupportedScopeList());
    }

    public function getSupportedScopeList(): array
    {
        return match ($this->apiVersion) {
            'v16.0' => [
                'pages_show_list',
                'read_insights',
                'pages_manage_posts',
                'pages_read_engagement',
                'pages_manage_engagement',
                'instagram_basic',
                'instagram_content_publish',
                'instagram_manage_insights',
                'instagram_manage_comments',
            ],
            default => [
                'business_management',
                'pages_show_list',
                'read_insights',
                'pages_manage_posts',
                'pages_read_engagement',
                'pages_manage_engagement',
                'instagram_basic',
                'instagram_content_publish',
                'instagram_manage_insights',
                'instagram_manage_comments',
            ]
        };
    }

    public function getAuthUrl(): string
    {
        return '';
    }

    public static function postConfigs(Closure $getAccountData): SocialProviderPostConfigs
    {
        return SocialProviderPostConfigs::make()
            ->simultaneousPosting(Util::config('social_provider_options.facebook_page.simultaneous_posting_on_multiple_accounts'))
            ->minTextChar(1)
            ->minTextChar(0, 'reel')
            ->minTextChar(0, 'story')
            ->minPhotos(1)
            ->minPhotos(0, 'reel')
            ->minVideos(1)
            ->minGifs(1)
            ->minGifs(0, 'reel')
            ->maxTextChar(Util::config('social_provider_options.facebook_page.post_character_limit'))
            ->maxPhotos(Util::config('social_provider_options.facebook_page.media_limit.photos'))
            ->maxPhotos(0, 'reel')
            ->maxVideos(Util::config('social_provider_options.facebook_page.media_limit.videos'))
            ->maxGifs(Util::config('social_provider_options.facebook_page.media_limit.gifs'))
            ->maxGifs(0, 'reel')
            ->maxGifs(0, 'story')
            ->allowMixingMediaTypes(Util::config('social_provider_options.facebook_page.allow_mixing'))
            ->allowMixingMediaTypes(false, 'reel');
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        return '#';
    }

    public static function externalAccountUrl(AccountResource $accountResource): string
    {
        return '';
    }

    public static function mapErrorMessage(string $key): string
    {
        return match ($key) {
            'access_token_expired' => __('mixpost::account.access_token_expired'),
            'no_media_selected' => __('mixpost::service.post.no_media_selected'),
            'reel_only_video_allowed' => __('mixpost::service.meta.reel_only_video_allowed'),
            'reel_supports_one_video' => __('mixpost::service.meta.reel_supports_one_video'),
            'story_single_media_limit' => __('mixpost::service.meta.story_single_media_limit'),
            'publication_video_expired' => __('mixpost::service.meta.error.publication_video_expired'),
            'session_expired' => __('mixpost::service.meta.error.session_expired'),
            'media_already_published' => __('mixpost::service.meta.error.media_already_published'),
            '100' => __('mixpost::service.meta.error.required_param_missing'),
            '1363040' => __('mixpost::media.aspect_ratio_range', ['min' => '16x9', 'max' => '9x16']),
            '1363127' => __('mixpost::media.resolution_range', ['min' => '540', 'max' => '960', 'recommended_min' => '1080', 'recommended_max' => '1920']),
            '1363128' => __('mixpost::media.duration_range', ['min' => '3', 'max' => '90']),
            '1363129' => __('mixpost::media.frame_rate_range', ['min' => '24', 'max' => '60']),
            'error_upload_video' => __('mixpost::error.error_upload_video'),
            'request_timeout' => __('mixpost::error.request_timeout'),
            'unknown_error' => __('mixpost::error.unknown_error'),
            default => $key,
        };
    }

    public static function supportPostDeletion(): bool|array
    {
        return false;
    }
}
