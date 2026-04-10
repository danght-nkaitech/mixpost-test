<?php

namespace Inovector\MixpostEnterprise\Abstracts;

use Closure;
use JsonSerializable;

abstract class FeatureLimitFormField implements JsonSerializable
{
    public string $name;

    public string $component;

    public mixed $value;

    public mixed $defaultCallback;

    public function __construct($name)
    {
        $this->name = $name;

        $this->default(null);

        $this->setValue(null);
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function default(?Closure $callback): static
    {
        $this->defaultCallback = $callback;

        return $this;
    }

    protected function resolveDefaultValue()
    {
        if (is_null($this->value) && $this->defaultCallback instanceof Closure) {
            return call_user_func($this->defaultCallback);
        }

        return $this->defaultCallback;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'component' => $this->component,
            'value' => $this->value ?? $this->resolveDefaultValue(),
        ];
    }

    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }
}
