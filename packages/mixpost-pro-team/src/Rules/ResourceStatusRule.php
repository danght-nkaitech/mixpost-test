<?php

namespace Inovector\Mixpost\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Inovector\Mixpost\Enums\ResourceStatus;

class ResourceStatusRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! in_array($value, [ResourceStatus::DISABLED->value, ResourceStatus::ENABLED->value])) {
            $fail('mixpost::rules.resource_status.invalid')->translate();
        }
    }
}
