<?php

namespace Inovector\Mixpost\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DomainURLRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^https?:\/\/(?:[a-zA-Z0-9-]+\.)?[a-zA-Z0-9-]+\.[a-zA-Z]{2,}$/', $value)) {
            $fail('mixpost::rules.domain_url_invalid')->translate();
        }
    }
}
