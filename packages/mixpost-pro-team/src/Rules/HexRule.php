<?php

namespace Inovector\Mixpost\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HexRule implements ValidationRule
{
    protected bool $forceFull;

    public function __construct(bool $forceFull = false)
    {
        $this->forceFull = $forceFull;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pattern = '/^#([a-fA-F0-9]{6}';

        if (! $this->forceFull) {
            $pattern .= '|[a-fA-F0-9]{3}';
        }

        $pattern .= ')$/';

        if (! preg_match($pattern, $value)) {
            $fail('mixpost::rules.hex.code_invalid')->translate();
        }
    }
}
