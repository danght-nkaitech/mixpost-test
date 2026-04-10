<?php

namespace Inovector\Mixpost;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inovector\Mixpost\Abstracts\SocialProviderManager as SocialProviderManagerAbstract;
use Inovector\Mixpost\Concerns\OAuth\UsesOAuthCodeChallenge;
use Inovector\Mixpost\Concerns\UsesCacheKey;
use Inovector\Mixpost\Exceptions\OAuthSessionExpired;
use Inovector\Mixpost\Facades\ServiceManager;
use Inovector\Mixpost\SocialProviders\Bluesky\BlueskyProvider;
use Inovector\Mixpost\SocialProviders\Google\GBPProvider;
use Inovector\Mixpost\SocialProviders\Google\YoutubeProvider;
use Inovector\Mixpost\SocialProviders\Linkedin\LinkedinPageProvider;
use Inovector\Mixpost\SocialProviders\Linkedin\LinkedinProvider;
use Inovector\Mixpost\SocialProviders\Mastodon\MastodonProvider;
use Inovector\Mixpost\SocialProviders\Meta\FacebookPageProvider;
use Inovector\Mixpost\SocialProviders\Meta\InstagramProvider;
use Inovector\Mixpost\SocialProviders\Pinterest\PinterestProvider;
use Inovector\Mixpost\SocialProviders\Threads\ThreadsProvider;
use Inovector\Mixpost\SocialProviders\TikTok\TikTokProvider;
use Inovector\Mixpost\SocialProviders\Twitter\TwitterProvider;

class SocialProviderManager extends SocialProviderManagerAbstract
{
    use UsesCacheKey;
    use UsesOAuthCodeChallenge;

    protected array $providers = [];

    public function providers(): array
    {
        if (! empty($this->providers)) {
            return $this->providers;
        }

        return $this->providers = [
            'twitter' => TwitterProvider::class,
            'facebook_page' => FacebookPageProvider::class,
            'instagram' => InstagramProvider::class,
            'threads' => ThreadsProvider::class,
            'mastodon' => MastodonProvider::class,
            'youtube' => YoutubeProvider::class,
            'gbp' => GBPProvider::class,
            'pinterest' => PinterestProvider::class,
            'linkedin' => LinkedinProvider::class,
            'linkedin_page' => LinkedinPageProvider::class,
            'tiktok' => TikTokProvider::class,
            'bluesky' => BlueskyProvider::class,
        ];
    }

    protected function connectTwitterProvider()
    {
        $config = ServiceManager::get('twitter', 'configuration');

        $config['redirect'] = route('mixpost.callbackSocialProvider', ['provider' => 'twitter']);

        return $this->buildConnectionProvider(TwitterProvider::class, $config);
    }

    protected function connectFacebookPageProvider()
    {
        $config = ServiceManager::get('facebook', 'configuration');

        $config['redirect'] = route('mixpost.callbackSocialProvider', ['provider' => 'facebook_page']);

        return $this->buildConnectionProvider(FacebookPageProvider::class, $config);
    }

    protected function connectInstagramProvider()
    {
        $config = ServiceManager::get('facebook', 'configuration');

        $config['redirect'] = route('mixpost.callbackSocialProvider', ['provider' => 'instagram']);

        return $this->buildConnectionProvider(InstagramProvider::class, $config);
    }

    protected function connectThreadsProvider()
    {
        $config = ServiceManager::get('threads', 'configuration');

        $config['redirect'] = route('mixpost.callbackSocialProvider', ['provider' => 'threads']);

        return $this->buildConnectionProvider(ThreadsProvider::class, $config);
    }

    protected function connectYoutubeProvider()
    {
        $config = ServiceManager::get('google', 'configuration');

        $config['redirect'] = route('mixpost.callbackSocialProvider', ['provider' => 'youtube']);

        return $this->buildConnectionProvider(YoutubeProvider::class, $config);
    }

    protected function connectGBPProvider()
    {
        $config = ServiceManager::get('google', 'configuration');

        $config['redirect'] = route('mixpost.callbackSocialProvider', ['provider' => 'gbp']);

        return $this->buildConnectionProvider(GBPProvider::class, $config);
    }

    protected function connectPinterestProvider()
    {
        $config = ServiceManager::get('pinterest', 'configuration');

        $config['redirect'] = route('mixpost.callbackSocialProvider', ['provider' => 'pinterest']);
        $config['values'] = [
            'environment' => $config['environment'] ?? 'sandbox',
        ];

        return $this->buildConnectionProvider(PinterestProvider::class, $config);
    }

    protected function connectLinkedinProvider()
    {
        $config = ServiceManager::get('linkedin', 'configuration');

        $config['redirect'] = route('mixpost.callbackSocialProvider', ['provider' => 'linkedin']);

        return $this->buildConnectionProvider(LinkedinProvider::class, $config);
    }

    protected function connectLinkedinPageProvider()
    {
        $config = ServiceManager::get('linkedin', 'configuration');

        $config['redirect'] = route('mixpost.callbackSocialProvider', ['provider' => 'linkedin_page']);

        return $this->buildConnectionProvider(LinkedinPageProvider::class, $config);
    }

    protected function connectTiktokProvider()
    {
        $config = ServiceManager::get('tiktok', 'configuration');

        $config['redirect'] = route('mixpost.callbackSocialProvider', ['provider' => 'tiktok']);

        return $this->buildConnectionProvider(TikTokProvider::class, $config);
    }

    protected function connectMastodonProvider()
    {
        $request = $this->container->request;
        $sessionServerKey = "{$this->config->get('mixpost.cache_prefix')}.mastodon_server";

        if ($request->route() && $request->route()->getName() === 'mixpost.accounts.add') {
            $serverName = $this->container->request->input('server');
            $request->session()->put($sessionServerKey, $serverName); // We keep the server name in the session. We'll need it in the callback
        } elseif ($request->route() && $request->route()->getName() === 'mixpost.callbackSocialProvider') {
            $serverName = $request->session()->get($sessionServerKey);
        } else {
            $serverName = $this->values['data']['server']; // Get the server value that have been set on SocialProviderManager::connect($provider, array $values = [])
        }

        $config = ServiceManager::get("mastodon.$serverName", 'configuration');

        $config['redirect'] = route('mixpost.callbackSocialProvider', ['provider' => 'mastodon']);

        $config['values'] = [
            'data' => [
                'server' => $serverName,
            ],
        ];

        return $this->buildConnectionProvider(MastodonProvider::class, $config);
    }

    protected function connectBlueskyProvider()
    {
        /* @var \Illuminate\Http\Request $request */
        $request = $this->container->request;

        $values = [
            'data' => [
                'server' => $this->values['data']['server'] ?? BlueskyProvider::DEFAULT_SERVER,
            ],
        ];

        $sessionServerKey = $this->resolveCacheKey('bluesky_server');

        // Handle form submission when adding a new BlueSky account
        if ($request->routeIs('mixpost.accounts.add')) {
            $input = array_filter([
                'service' => $request->input('service'),
                'server' => $request->input('server'),
            ]);

            Validator::make($input, [
                'service' => ['required', 'string', Rule::in(['bluesky', 'custom'])],
                'server' => ['sometimes', 'required_if:service,custom', 'string', 'url'],
            ])->validate();

            $values['data']['server'] = $input['service'] === 'custom' ? $input['server'] : BlueskyProvider::DEFAULT_SERVER;
            $request->session()->put($sessionServerKey, $values['data']['server']);

            $this->setCodeVerifierSession($request, $this->getCodeVerifier());
        }

        // Handle the callback after the user authorizes the BlueSky account
        if ($request->routeIs('mixpost.callbackSocialProvider')) {
            $values['data']['server'] = $request->session()->get($sessionServerKey);

            if (! $values['data']['server']) {
                throw new OAuthSessionExpired('Bluesky server name is missing. Possible reasons: the server name was not set in the session, or the session has expired.');
            }

            $request->session()->forget($sessionServerKey);
        }

        $config = [
            'client_id' => route('mixpost.blueskyOauth.clientMeta'),
            'client_secret' => '',  // No client secret is required for Bluesky
            'redirect' => route('mixpost.callbackSocialProvider', ['provider' => 'bluesky']),
            'values' => $values,
        ];

        return $this->buildConnectionProvider(BlueskyProvider::class, $config);
    }
}
