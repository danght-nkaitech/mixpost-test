<?php

namespace Inovector\Mixpost\SocialProviders\Threads;

use Illuminate\Support\Arr;
use Inovector\Mixpost\Abstracts\SocialProvider;
use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\Services\ThreadsService;
use Inovector\Mixpost\SocialProviders\Threads\Concerns\ManagesCallbacks;
use Inovector\Mixpost\SocialProviders\Threads\Concerns\ManagesConfig;
use Inovector\Mixpost\SocialProviders\Threads\Concerns\ManagesOAuth;
use Inovector\Mixpost\SocialProviders\Threads\Concerns\ManagesResources;
use Inovector\Mixpost\SocialProviders\Threads\Concerns\UsesResponseBuilder;

class ThreadsProvider extends SocialProvider
{
    use ManagesCallbacks;
    use ManagesConfig;
    use ManagesOAuth;
    use ManagesResources;
    use UsesResponseBuilder;

    public array $callbackResponseKeys = ['code'];

    public string $graphUrl = 'https://graph.threads.net';

    public string $graphVersion = 'v1.0';

    public static function service(): string
    {
        return ThreadsService::class;
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        $data = json_decode($accountResource->pivot->data ?? '{}', true);

        return Arr::get($data, 'permalink', '');
    }

    public static function externalAccountUrl(AccountResource $accountResource): string
    {
        return "https://www.threads.com/@$accountResource->username";
    }

    public static function supportPostDeletion(): bool|array
    {
        return true;
    }
}
