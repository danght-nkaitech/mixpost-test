<?php

namespace Inovector\Mixpost\Configs;

use Illuminate\Validation\Rule;
use Inovector\Mixpost\Abstracts\Config;
use Inovector\Mixpost\Facades\AIManager;

class AIConfig extends Config
{
    public function group(): string
    {
        return 'ai';
    }

    public function form(): array
    {
        return [
            'provider' => '',
            'instructions' => '',
        ];
    }

    public function rules(): array
    {
        return [
            'provider' => ['sometimes', 'nullable', Rule::in(AIManager::getProviderSelectionOptionKeys())],
            'instructions' => ['sometimes', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'instructions.max' => __('validation.max.string', ['attribute' => 'Instructions', 'max' => 1000]),
        ];
    }
}
