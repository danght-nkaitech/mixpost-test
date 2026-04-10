<?php

namespace Inovector\MixpostEnterprise\Configs;

use Inovector\Mixpost\Abstracts\Config;

class ScriptsConfig extends Config
{
    public function group(): string
    {
        return 'scripts';
    }

    public function form(): array
    {
        return [
            'head' => '',
            'body' => '',
        ];
    }

    public function rules(): array
    {
        return [
            'head' => ['sometimes', 'nullable', 'max:6000'],
            'body' => ['sometimes', 'nullable', 'max:6000'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
