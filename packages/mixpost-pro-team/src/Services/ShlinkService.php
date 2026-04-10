<?php

namespace Inovector\Mixpost\Services;

use Inovector\Mixpost\Abstracts\Service;
use Inovector\Mixpost\Enums\ServiceGroup;
use Inovector\Mixpost\Rules\DomainURLRule;

class ShlinkService extends Service
{
    public static function group(): ServiceGroup
    {
        return ServiceGroup::URL_SHORTENER;
    }

    public static function form(): array
    {
        return [
            'api_key' => '',
            'domain_url' => '',
        ];
    }

    public static function formRules(): array
    {
        return [
            'api_key' => ['required'],
            'domain_url' => ['required', new DomainURLRule],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'api_key' => __('validation.required', ['attribute' => 'API Key']),
        ];
    }
}
