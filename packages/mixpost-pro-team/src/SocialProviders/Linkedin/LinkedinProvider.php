<?php

namespace Inovector\Mixpost\SocialProviders\Linkedin;

use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Abstracts\SocialProvider;
use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\Contracts\SocialProviderPostOptions as SocialProviderPostOptionsContract;
use Inovector\Mixpost\Services\LinkedInService;
use Inovector\Mixpost\SocialProviders\Linkedin\Concerns\ManagesConfig;
use Inovector\Mixpost\SocialProviders\Linkedin\Concerns\ManagesOAuth;
use Inovector\Mixpost\SocialProviders\Linkedin\Concerns\ManagesRateLimit;
use Inovector\Mixpost\SocialProviders\Linkedin\Concerns\ManagesResources;
use Inovector\Mixpost\SocialProviders\Linkedin\Support\LinkedinPostOptions;
use Inovector\Mixpost\Support\SocialProviderPostConfigs;
use Inovector\Mixpost\Util;

class LinkedinProvider extends SocialProvider
{
    use ManagesConfig;
    use ManagesOAuth;
    use ManagesRateLimit;
    use ManagesResources;

    public array $callbackResponseKeys = ['code'];

    protected array $scope;

    public string $apiVersion = 'v2';

    public string $apiUrl = 'https://api.linkedin.com';

    public function __construct(Request $request, string $clientId, string $clientSecret, string $redirectUrl, array $values = [])
    {
        $this->setScope();

        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $values);
    }

    public static function name(): string
    {
        return 'LinkedIn';
    }

    public static function service(): string
    {
        return LinkedInService::class;
    }

    protected function setScope(): void
    {
        $this->scope = match (self::getProduct()) {
            'sign_share' => ['r_liteprofile', 'r_emailaddress', 'w_member_social'],
            'sign_open_id_share' => ['openid', 'profile', 'w_member_social'],
            'community_management' => ['w_member_social', 'w_member_social_feed', 'r_basicprofile', 'r_organization_social', 'r_organization_social_feed', 'w_organization_social', 'w_organization_social_feed', 'rw_organization_admin'],
            default => []
        };
    }

    public function httpHeaders(): array
    {
        return [
            'X-Restli-Protocol-Version' => '2.0.0',
        ];
    }

    public function httpHeadersNext(): array
    {
        return [
            'Linkedin-Version' => '202507',
        ];
    }

    public static function postConfigs(Closure $getAccountData): SocialProviderPostConfigs
    {
        return SocialProviderPostConfigs::make()
            ->simultaneousPosting(Util::config('social_provider_options.linkedin.simultaneous_posting_on_multiple_accounts'))
            ->minTextChar(1)
            ->maxTextChar(Util::config('social_provider_options.linkedin.post_character_limit'))
            ->minPhotos(1)
            ->minVideos(1)
            ->minGifs(1)
            ->maxPhotos(Util::config('social_provider_options.linkedin.media_limit.photos'))
            ->maxVideos(1)
            ->maxGifs(Util::config('social_provider_options.linkedin.media_limit.gifs'))
            ->allowMixingMediaTypes(Util::config('social_provider_options.linkedin.allow_mixing'))
            ->enableVideoThumb(true);
    }

    public static function postOptions(): SocialProviderPostOptionsContract
    {
        return new LinkedinPostOptions;
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        return "https://linkedin.com/feed/update/{$accountResource->pivot->provider_post_id}";
    }

    public static function supportAnalytics(): bool
    {
        return false;
    }

    public static function externalAccountUrl(AccountResource $accountResource): string
    {
        return "https://www.linkedin.com/in/$accountResource->username";
    }

    public static function supportPostDeletion(): bool|array
    {
        return true;
    }
}
