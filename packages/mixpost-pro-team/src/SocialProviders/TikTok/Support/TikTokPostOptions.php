<?php

namespace Inovector\Mixpost\SocialProviders\TikTok\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Support\SocialProviderPostOptions;

class TikTokPostOptions extends SocialProviderPostOptions
{
    public function rules(FormRequest $request): array
    {
        return [
            'privacy_level' => ['sometimes', 'array'],
            'privacy_level.*' => ['sometimes', 'string', 'nullable', 'in:PUBLIC_TO_EVERYONE,MUTUAL_FOLLOW_FRIENDS,SELF_ONLY,FOLLOWER_OF_CREATOR'],
            'allow_comments' => ['sometimes', 'array'],
            'allow_comments.*' => ['sometimes', 'boolean'],
            'allow_duet' => ['sometimes', 'array'],
            'allow_duet.*' => ['sometimes', 'boolean'],
            'allow_stitch' => ['sometimes', 'array'],
            'allow_stitch.*' => ['sometimes', 'boolean'],
            'is_aigc' => ['sometimes', 'array'],
            'is_aigc.*' => ['sometimes', 'boolean'],
            'content_disclosure' => ['sometimes', 'array'],
            'content_disclosure.*' => ['sometimes', 'boolean'],
            'brand_content_toggle' => ['sometimes', 'array'],
            'brand_content_toggle.*' => ['sometimes', 'boolean'],
            'brand_organic_toggle' => ['sometimes', 'array'],
            'brand_organic_toggle.*' => ['sometimes', 'boolean'],
        ];
    }

    public function map(array $options = []): array
    {
        return [
            'privacy_level' => Arr::get($options, 'privacy_level', ['account-0' => '']),
            'allow_comments' => Arr::map(Arr::get($options, 'allow_comments', ['account-0' => false]), function ($value) {
                return (bool) $value;
            }),
            'allow_duet' => Arr::map(Arr::get($options, 'allow_duet', ['account-0' => false]), function ($value) {
                return (bool) $value;
            }),
            'allow_stitch' => Arr::map(Arr::get($options, 'allow_stitch', ['account-0' => false]), function ($value) {
                return (bool) $value;
            }),
            'is_aigc' => Arr::map(Arr::wrap(Arr::get($options, 'is_aigc', ['account-0' => false])), function ($value) {
                return (bool) $value;
            }),
            'content_disclosure' => Arr::map(Arr::wrap(Arr::get($options, 'content_disclosure', ['account-0' => false])), function ($value) {
                return (bool) $value;
            }),
            'brand_organic_toggle' => Arr::map(Arr::wrap(Arr::get($options, 'brand_organic_toggle', ['account-0' => false])), function ($value) {
                return (bool) $value;
            }),
            'brand_content_toggle' => Arr::map(Arr::wrap(Arr::get($options, 'brand_content_toggle', ['account-0' => false])), function ($value) {
                return (bool) $value;
            }),
        ];
    }
}
