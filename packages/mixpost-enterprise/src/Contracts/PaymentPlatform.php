<?php

namespace Inovector\MixpostEnterprise\Contracts;

interface PaymentPlatform
{
    public static function name(): string;

    public static function readableName(): string;

    public static function component(): string;

    public static function formCredentials(): array;

    public static function formOptions(): array;

    public static function formRules(): array;

    public static function formMessages(): array;
}
