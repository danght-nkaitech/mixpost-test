<?php

namespace Inovector\Mixpost\Services;

use Illuminate\Validation\Rule;
use Inovector\Mixpost\Abstracts\Service;
use Inovector\Mixpost\Enums\ServiceGroup;

class PinterestService extends Service
{
    public static array $exposedFormAttributes = ['environment'];

    public static function group(): ServiceGroup
    {
        return ServiceGroup::SOCIAL;
    }

    public static function form(): array
    {
        return [
            'client_id' => '',
            'client_secret' => '',
            'environment' => 'sandbox',
        ];
    }

    public static function formRules(): array
    {
        return [
            'client_id' => ['required'],
            'client_secret' => ['required'],
            'environment' => ['required', Rule::in(['sandbox', 'production'])],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'client_id' => __('validation.required', ['attribute' => 'App ID']),
            'client_secret' => __('validation.required', ['attribute' => 'APP Secret key']),
            'environment' => __('validation.required', ['attribute' => 'Environment']),
        ];
    }
}
