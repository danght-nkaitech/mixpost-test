<?php

namespace Inovector\Mixpost\Services;

use Inovector\Mixpost\Abstracts\Service;
use Inovector\Mixpost\Enums\ServiceGroup;

class OpenAIService extends Service
{
    public static function group(): ServiceGroup
    {
        return ServiceGroup::AI;
    }

    public static array $exposedFormAttributes = ['model'];

    public static function name(): string
    {
        return 'openai';
    }

    public static function form(): array
    {
        return [
            'secret_key' => '',
        ];
    }

    public static function formRules(): array
    {
        return [
            'secret_key' => ['required'],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'secret_key' => __('validation.required', ['attribute' => 'API Key']),
        ];
    }
}
