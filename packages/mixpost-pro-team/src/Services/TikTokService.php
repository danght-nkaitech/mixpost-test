<?php

namespace Inovector\Mixpost\Services;

use Inovector\Mixpost\Abstracts\Service;
use Inovector\Mixpost\Enums\ServiceGroup;

class TikTokService extends Service
{
    public static array $exposedFormAttributes = ['share_type'];

    public static function group(): ServiceGroup
    {
        return ServiceGroup::SOCIAL;
    }

    public static function name(): string
    {
        return 'tiktok';
    }

    public static function form(): array
    {
        return [
            'client_id' => '',
            'client_secret' => '',
            'share_type' => 'inbox',
        ];
    }

    public static function formRules(): array
    {
        return [
            'client_id' => ['required'],
            'client_secret' => ['required'],
            'share_type' => ['required'],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'client_id' => __('validation.required', ['attribute' => 'Client Key']),
            'client_secret' => __('validation.required', ['attribute' => 'Client Secret']),
            'share_type' => __('validation.required', ['attribute' => 'Share type']),
        ];
    }
}
