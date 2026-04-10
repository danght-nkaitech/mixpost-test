<?php

namespace Inovector\Mixpost\Configs;

use Illuminate\Validation\Rule;
use Inovector\Mixpost\Abstracts\Config;
use Inovector\Mixpost\Facades\UrlShortenerManager;

class GeneralConfig extends Config
{
    public function group(): string
    {
        return 'general';
    }

    public function form(): array
    {
        return [
            'url_shortener_provider' => 'disabled',
        ];
    }

    public function rules(): array
    {
        return [
            'url_shortener_provider' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(array_merge(UrlShortenerManager::getProviderSelectionOptionKeys(), ['disabled'])),
            ],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
