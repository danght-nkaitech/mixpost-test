<?php

namespace Inovector\Mixpost\Support;

use Inovector\Mixpost\Contracts\WebhookEvent;

class AnonymousWebhookEvent implements WebhookEvent
{
    protected static string $name;

    protected array $payload;

    public function __construct(string $name, array $payload = [])
    {
        self::$name = $name;
        $this->payload = $payload;
    }

    public static function name(): string
    {
        return self::$name;
    }

    public static function nameLocalized(): string
    {
        return self::name();
    }

    public function payload(): array
    {
        return $this->payload;
    }
}
