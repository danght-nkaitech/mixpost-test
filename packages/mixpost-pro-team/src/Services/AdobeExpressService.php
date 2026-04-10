<?php

namespace Inovector\Mixpost\Services;

use Inovector\Mixpost\Abstracts\Service;
use Inovector\Mixpost\Enums\ServiceGroup;

class AdobeExpressService extends Service
{
    public static array $exposedFormAttributes = ['client_id', 'app_name'];

    public static function group(): ServiceGroup
    {
        return ServiceGroup::MEDIA;
    }

    public static function form(): array
    {
        return [
            'client_id' => '',
        ];
    }

    public static function formRules(): array
    {
        return [
            'client_id' => ['required'],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'client_id' => __('validation.required', ['attribute' => 'App ID']),
        ];
    }
}
