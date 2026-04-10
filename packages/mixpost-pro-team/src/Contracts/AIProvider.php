<?php

namespace Inovector\Mixpost\Contracts;

use Inovector\Mixpost\Responses\AIProviderResponse;

interface AIProvider
{
    public static function name(): string;

    public static function nameLocalized(): string;

    public static function service(): string;

    public function generateText(string $prompt, string $instructions = ''): AIProviderResponse;

    public function generateImage(): AIProviderResponse;
}
