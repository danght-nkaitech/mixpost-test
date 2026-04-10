<?php

namespace Inovector\MixpostEnterprise\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Inovector\MixpostEnterprise\Util;

class CurrencyRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Util::currencies()->contains('code', $value)) {
            $fail(__('mixpost-enterprise::rules.currency.invalid_currency'));
        }
    }
}
