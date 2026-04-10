<?php

namespace Inovector\Mixpost\Services;

use Inovector\Mixpost\Abstracts\Service;
use Inovector\Mixpost\Enums\ServiceGroup;
use Inovector\Mixpost\Rules\DomainURLRule;

class YourlsService extends Service
{
    public static function group(): ServiceGroup
    {
        return ServiceGroup::URL_SHORTENER;
    }

    public static function form(): array
    {
        return [
            'signature' => '',
            'domain_url' => '',
        ];
    }

    public static function formRules(): array
    {
        return [
            'signature' => ['required'],
            'domain_url' => ['required', new DomainURLRule],
        ];
    }

    public static function formMessages(): array
    {
        return [
            'signature' => __('validation.required', ['attribute' => 'Signature']),
            'domain_url' => [
                'required' => __('validation.required', ['attribute' => 'Domain']),
            ],
        ];
    }
}
