<?php

namespace Inovector\Mixpost\SocialProviders\Google\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Support\SocialProviderPostOptions;

class YoutubePostOptions extends SocialProviderPostOptions
{
    public function rules(FormRequest $request): array
    {
        return [
            'title' => ['sometimes', 'nullable', 'string', 'max:256'],
            'status' => ['sometimes', 'string', 'in:public,private,unlisted'],
            'made_for_kids' => ['sometimes', 'boolean'],
        ];
    }

    public function map(array $options = []): array
    {
        return [
            'title' => Arr::get($options, 'title', ''),
            'status' => Arr::get($options, 'status', 'public'),
            'made_for_kids' => Arr::get($options, 'made_for_kids', false),
        ];
    }
}
