<?php

namespace Inovector\Mixpost\Services;

use Inovector\Mixpost\Abstracts\Service;
use Inovector\Mixpost\Enums\ServiceGroup;

class BitlyService extends Service
{
    public static function group(): ServiceGroup
    {
        return ServiceGroup::URL_SHORTENER;
    }

    public static function form(): array
    {
        return [
            'token' => '',
        ];
    }

    public static function formRules(): array
    {
        return [
            'token' => ['required'],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'token' => __('validation.required', ['attribute' => 'Token']),
        ];
    }
}
