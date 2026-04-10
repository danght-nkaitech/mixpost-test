<?php

namespace Inovector\Mixpost\Configs;

use Inovector\Mixpost\Abstracts\Config;

class GifConfig extends Config
{
    private const VALID_PROVIDERS = ['tenor', 'giphy'];

    public function group(): string
    {
        return 'gif';
    }

    public function form(): array
    {
        return [
            'gif_provider' => 'giphy',
        ];
    }

    public function persistData(string $name, mixed $payload): void
    {
        if ($name === 'gif_provider' && ! in_array($payload, self::VALID_PROVIDERS)) {
            throw new \InvalidArgumentException('Invalid GIF provider config');
        }

        parent::persistData($name, $payload);
    }

    public function rules(): array
    {
        return [
            'gif_provider' => ['required', 'nullable', 'string', 'in:' . implode(',', self::VALID_PROVIDERS)],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
