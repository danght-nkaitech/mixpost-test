<?php

namespace Inovector\Mixpost\Support;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Contracts\SocialProviderPostOptions as SocialProviderPostOptionsContract;

class SocialProviderPostOptions implements SocialProviderPostOptionsContract
{
    public function rules(FormRequest $request): array
    {
        return [];
    }

    public function map(array $options = []): array
    {
        return [];
    }
}
