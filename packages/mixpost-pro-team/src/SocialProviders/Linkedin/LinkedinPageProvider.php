<?php

namespace Inovector\Mixpost\SocialProviders\Linkedin;

use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\SocialProviders\Linkedin\Concerns\ManagesPageResources;
use Inovector\Mixpost\SocialProviders\Linkedin\Enums\PageType;

class LinkedinPageProvider extends LinkedinProvider
{
    use ManagesPageResources;

    public bool $onlyUserAccount = false;

    public static function supportAnalytics(): bool
    {
        return true;
    }

    public static function externalAccountUrl(AccountResource $accountResource): string
    {
        $pageType = $accountResource->data['page_type'] ?? PageType::COMPANY->value;

        return "https://www.linkedin.com/$pageType/$accountResource->username";
    }
}
