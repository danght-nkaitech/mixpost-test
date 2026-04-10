<?php

namespace Inovector\Mixpost\Services\Bluesky;

use Inovector\Mixpost\Abstracts\Service;
use Inovector\Mixpost\Enums\ServiceGroup;

class BlueskyService extends Service
{
    public static function group(): ServiceGroup
    {
        return ServiceGroup::SOCIAL;
    }

    public static function form(): array
    {
        return [
            'private_key' => '',
        ];
    }

    public static function formRules(): array
    {
        return [
            'private_key' => ['required'],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'client_id' => __('validation.required', ['attribute' => 'Private Key']),
        ];
    }
}
