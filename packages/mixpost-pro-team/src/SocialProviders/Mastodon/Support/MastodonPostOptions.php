<?php

namespace Inovector\Mixpost\SocialProviders\Mastodon\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Support\SocialProviderPostOptions;

class MastodonPostOptions extends SocialProviderPostOptions
{
    public function rules(FormRequest $request): array
    {
        return [
            'sensitive' => ['sometimes', 'boolean'],
        ];
    }

    public function map(array $options = []): array
    {
        return [
            'sensitive' => (bool) Arr::get($options, 'sensitive', false),
        ];
    }
}
