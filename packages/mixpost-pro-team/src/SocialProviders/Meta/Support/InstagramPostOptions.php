<?php

namespace Inovector\Mixpost\SocialProviders\Meta\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Support\SocialProviderPostOptions;

class InstagramPostOptions extends SocialProviderPostOptions
{
    public function rules(FormRequest $request): array
    {
        return [
            'type' => ['sometimes', 'string', 'in:post,reel,story'],
        ];
    }

    public function map(array $options = []): array
    {
        return [
            'type' => Arr::get($options, 'type', 'post'),
        ];
    }
}
