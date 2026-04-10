<?php

namespace Inovector\Mixpost\SocialProviders\Google\Concerns;

use Illuminate\Support\Str;

trait GBPUsesValues
{
    public function accountId(): string
    {
        return Str::after($this->values['data']['account']['name'], 'accounts/');
    }

    public function locationId(): string
    {
        return Str::after($this->values['provider_id'], 'locations/');
    }
}
