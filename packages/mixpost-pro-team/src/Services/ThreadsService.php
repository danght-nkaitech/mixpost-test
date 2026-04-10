<?php

namespace Inovector\Mixpost\Services;

use Inovector\Mixpost\Abstracts\Service;
use Inovector\Mixpost\Enums\ServiceGroup;

class ThreadsService extends Service
{
    public static function group(): ServiceGroup
    {
        return ServiceGroup::SOCIAL;
    }

    public static function form(): array
    {
        return [
            'client_id' => '',
            'client_secret' => '',
        ];
    }

    public static function formRules(): array
    {
        return [
            'client_id' => ['required'],
            'client_secret' => ['required'],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'client_id' => __('validation.required', ['attribute' => 'App ID']),
            'client_secret' => __('validation.required', ['attribute' => 'APP Secret']),
        ];
    }
}
