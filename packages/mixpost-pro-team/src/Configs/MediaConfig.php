<?php

namespace Inovector\Mixpost\Configs;

use Inovector\Mixpost\Abstracts\Config;

class MediaConfig extends Config
{
    public function group(): string
    {
        return 'media';
    }

    public function form(): array
    {
        return [
            'stock_photo_provider' => 'unsplash',
        ];
    }

    public function rules(): array
    {
        return [
            'stock_photo_provider' => ['required', 'nullable', 'string', 'in:unsplash,pexels'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
