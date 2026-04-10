<?php

namespace Inovector\Mixpost\Contracts;

interface UrlShortenerProvider
{
    public static function name(): string;

    public static function nameLocalized(): string;

    public function shortenUrl(string $originalUrl): array; // TODO: Implement return type for shortenUrl method, e.g., AIProviderResponse
}
