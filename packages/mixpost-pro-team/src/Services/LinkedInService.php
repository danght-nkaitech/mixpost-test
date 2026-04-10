<?php

namespace Inovector\Mixpost\Services;

use Illuminate\Validation\Rule;
use Inovector\Mixpost\Abstracts\Service;
use Inovector\Mixpost\Enums\ServiceGroup;

class LinkedInService extends Service
{
    public static function group(): ServiceGroup
    {
        return ServiceGroup::SOCIAL;
    }

    public static array $exposedFormAttributes = ['product'];

    public static function name(): string
    {
        return 'linkedin';
    }

    public static function form(): array
    {
        return [
            'client_id' => '',
            'client_secret' => '',
            'product' => 'sign_open_id_share',
        ];
    }

    public static function formRules(): array
    {
        return [
            'client_id' => ['required'],
            'client_secret' => ['required'],
            'product' => ['required', Rule::in(['sign_share', 'sign_open_id_share', 'community_management'])],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'client_id' => __('validation.required', ['attribute' => 'Client ID']),
            'client_secret' => __('validation.required', ['attribute' => 'Client Secret']),
            'product' => __('validation.in', ['attribute' => 'Product']),
        ];
    }
}
