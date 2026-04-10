<?php

namespace Inovector\Mixpost\SocialProviders\Linkedin\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Support\SocialProviderPostOptions;

class LinkedinPostOptions extends SocialProviderPostOptions
{
    public function rules(FormRequest $request): array
    {
        return [
            'visibility' => ['required', 'in:PUBLIC,CONNECTIONS'],
        ];
    }

    public function map(array $options = []): array
    {
        return [
            'visibility' => Arr::get($options, 'visibility', 'PUBLIC'),
        ];
    }
}
