<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky;

use Inovector\Mixpost\Abstracts\SocialProvider;
use Inovector\Mixpost\Services\Bluesky\BlueskyService;
use Inovector\Mixpost\SocialProviders\Bluesky\Concerns\HasExternalUrls;
use Inovector\Mixpost\SocialProviders\Bluesky\Concerns\ManagesAccount;
use Inovector\Mixpost\SocialProviders\Bluesky\Concerns\ManagesConfig;
use Inovector\Mixpost\SocialProviders\Bluesky\Concerns\ManagesMetrics;
use Inovector\Mixpost\SocialProviders\Bluesky\Concerns\ManagesOAuth;
use Inovector\Mixpost\SocialProviders\Bluesky\Concerns\ManagesPost;

class BlueskyProvider extends SocialProvider
{
    use HasExternalUrls;
    use ManagesAccount;
    use ManagesConfig;
    use ManagesMetrics;
    use ManagesOAuth;
    use ManagesPost;

    public const DEFAULT_SERVER = 'https://bsky.social';

    public array $callbackResponseKeys = ['code'];

    public static function service(): string
    {
        return BlueskyService::class;
    }

    public static function supportPostDeletion(): bool|array
    {
        return true;
    }
}
