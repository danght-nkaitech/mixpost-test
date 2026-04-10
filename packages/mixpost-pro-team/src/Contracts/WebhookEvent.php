<?php

namespace Inovector\Mixpost\Contracts;

interface WebhookEvent
{
    public static function name(): string;

    public static function nameLocalized(): string;

    public function payload(): array;
}
