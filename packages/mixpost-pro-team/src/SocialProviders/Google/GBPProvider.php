<?php

namespace Inovector\Mixpost\SocialProviders\Google;

use Inovector\Mixpost\Abstracts\SocialProvider;
use Inovector\Mixpost\Services\GoogleService;
use Inovector\Mixpost\SocialProviders\Google\Concerns\GBPManagesAccount;
use Inovector\Mixpost\SocialProviders\Google\Concerns\GBPManagesConfig;
use Inovector\Mixpost\SocialProviders\Google\Concerns\GBPManagesPost;
use Inovector\Mixpost\SocialProviders\Google\Concerns\ManagesOAuth;
use Inovector\Mixpost\SocialProviders\Google\Concerns\UsesResponseBuilder;

class GBPProvider extends SocialProvider
{
    use GBPManagesAccount;
    use GBPManagesConfig;
    use GBPManagesPost;
    use ManagesOAuth;
    use UsesResponseBuilder;

    public bool $onlyUserAccount = false;

    public array $callbackResponseKeys = ['code'];

    protected string $apiVersion = 'v4';

    protected string $apiUrl = 'https://mybusiness.googleapis.com';

    public static function name(): string
    {
        return 'Google Business Profile';
    }

    public static function service(): string
    {
        return GoogleService::class;
    }

    protected function getScopes(): array
    {
        return [
            'https://www.googleapis.com/auth/business.manage',
        ];
    }

    public static function supportPostDeletion(): bool|array
    {
        return true;
    }
}
