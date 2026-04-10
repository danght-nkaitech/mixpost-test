<?php

namespace Inovector\Mixpost\SocialProviders\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Abstracts\SocialProvider;
use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\Services\TwitterService;
use Inovector\Mixpost\SocialProviders\Twitter\Concerns\ManagesConfig;
use Inovector\Mixpost\SocialProviders\Twitter\Concerns\ManagesOAuth;
use Inovector\Mixpost\SocialProviders\Twitter\Concerns\ManagesRateLimit;
use Inovector\Mixpost\SocialProviders\Twitter\Concerns\ManagesResources;
use Inovector\Mixpost\Support\SocialProviderPostConfigs;
use Inovector\Mixpost\Util;

class TwitterProvider extends SocialProvider
{
    use ManagesConfig;
    use ManagesOAuth;
    use ManagesRateLimit;
    use ManagesResources;

    public array $callbackResponseKeys = ['oauth_token', 'oauth_verifier'];

    protected string $apiVersion = '2';

    public TwitterOAuth $connection;

    // Overwrite __construct to use Twitter SDK
    public function __construct(Request $request, string $clientId, string $clientSecret, string $redirectUrl, array $values = [])
    {
        $this->connection = new TwitterOAuth($clientId, $clientSecret);
        $this->connection->setApiVersion($this->apiVersion);
        $this->connection->setTimeouts(10, 60);

        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $values);
    }

    public static function name(): string
    {
        return 'X';
    }

    public static function service(): string
    {
        return TwitterService::class;
    }

    public static function postConfigs(Closure $getAccountData): SocialProviderPostConfigs
    {
        return SocialProviderPostConfigs::make()
            ->simultaneousPosting(Util::config('social_provider_options.twitter.simultaneous_posting_on_multiple_accounts'))
            ->minTextChar(1)
            ->maxTextChar($getAccountData('verified') ? 25000 : 280)
            ->minPhotos(1)
            ->minVideos(1)
            ->minGifs(1)
            ->maxPhotos(Util::config('social_provider_options.twitter.media_limit.photos'))
            ->maxVideos(Util::config('social_provider_options.twitter.media_limit.videos'))
            ->maxGifs(Util::config('social_provider_options.twitter.media_limit.gifs'))
            ->allowMixingMediaTypes(Util::config('social_provider_options.twitter.allow_mixing'));
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        return "https://twitter.com/$accountResource->username/status/{$accountResource->pivot->provider_post_id}";
    }

    public static function externalAccountUrl(AccountResource $accountResource): string
    {
        return "https://x.com/$accountResource->username";
    }

    public static function mapErrorMessage(string $key): string
    {
        return match ($key) {
            'access_token_expired' => __('mixpost::account.access_token_expired'),
            'upload_failed' => __('mixpost::service.twitter.upload_failed'),
            default => $key,
        };
    }

    public static function supportPostDeletion(): bool|array
    {
        return true;
    }
}
